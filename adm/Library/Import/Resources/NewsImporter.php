<?php

namespace Adm\Library\Import\Resources;

use Adm\Library\Import\AbstractImporter;
use App\Models\Url;
use Modules\News\Config\NewsArticlesConfig;
use Modules\News\Models\NewsArticle;
use Modules\News\Models\NewsAuthor;
use Modules\News\Models\NewsCategory;

class NewsImporter extends AbstractImporter
{
    /**
     * {@inheritdoc}
     */
    public $resource_label   = 'News articles';

    /**
     * Local reference to the news listing page
     * 
     * @var ?\Illuminate\Database\Eloquent\Model
     */
    protected $ListingPage      = null;

    /**
     * Local reference to a news category page
     * 
     * @var ?\Illuminate\Database\Eloquent\Model
     */
    protected $Category         = null;

    /**
     * Local array map of author names & IDs
     * 
     * @var array
     */
    protected $author_id_map    = [];

    /**
     * {@inheritdoc}
     */
    public function importData(): void
    {
        $to_import = $this->parseImportSheet(storage_path('import/news-import.csv'));
        foreach ($to_import as $row) {
            if (isset($row['category']) && !empty($row['category'])) {
                $this->setCategory($row['category']);
            }

            $Article = $this->getExistingRecord($row['url']);
            $article_ts = strtotime($row['date']);
            $import_folder = 'News/' . date("Y", $article_ts) . '/' . date("F", $article_ts);

            if (!empty($row['content']) && !empty($row['content']['images'])) {
                $row['content'] = $this->importMapWysiwygImages(
                    $row['content']['content'],
                    $row['content']['images'],
                    $import_folder
                );
            } else {
                $row['content'] = $row['content']['content'];
            }

            $Article->news_category_id = $this->Category ? $this->Category->news_category_id : 0;
            $Article->news_author_id = !empty($row['author']) ? $this->findAuthorID($row['author']) : 0;
            $Article->title = $row['title'];
            $Article->h1_title = $row['h1_title'];
            $Article->excerpt = $row['excerpt'];
            $Article->content = $row['content'];
            $Article->article_date = $row['date'];
            $Article->status = $row['status'];

            if (empty($Article->image)) {
                $Article->image = 0;
            }

            $Article->save();

            if (!empty($row['image'])) {
                $this->importImage(
                    $row['image'],
                    $Article,
                    'news_article_id',
                    'image',
                    'desktop',
                    NewsArticlesConfig::$fields['image']['sizes']['desktop'],
                    $import_folder
                );
            }

            $url_settings = $this->getUrlAndRedirect($row['url']);
            $this->storeUrl(
                $url_settings['url'],
                $row['meta_title'],
                $row['meta_description'],
                $row['no_index'],
                $Article->url_record,
                $Article->news_article_id
            );
            $this->createRedirect($url_settings['redirect']['from'], $url_settings['redirect']['to']);

            $this->outputMessage('Article ' . $Article->title . ' imported.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getImportFieldConfiguration(): array
    {
        return [
            'Title'             => [
                'field_name'        => 'title',
                'type'              => 'string'
            ],
            'H1 title'          => [
                'field_name'        => 'h1_title',
                'type'              => 'string'
            ],
            'Image'             => [
                'field_name'        => 'image',
                'type'              => 'image'
            ],
            'Date'              => [
                'field_name'        => 'date',
                'type'              => 'date'
            ],
            'Category'          => [
                'field_name'        => 'category',
                'type'              => 'string'
            ],
            'Excerpt'           => [
                'field_name'        => 'excerpt',
                'type'              => 'textarea'
            ],
            'Content'           => [
                'field_name'        => 'content',
                'type'              => 'wysiwyg'
            ],
            'Author'            => [
                'field_name'        => 'author',
                'type'              => 'string'
            ],
            'Status'            => [
                'field_name'        => 'status',
                'type'              => 'boolean'
            ],
            'URL'               => [
                'field_name'        => 'url',
                'type'              => 'string'
            ],
            'Meta title'        => [
                'field_name'        => 'meta_title',
                'type'              => 'string'
            ],
            'Meta description'  => [
                'field_name'        => 'meta_description',
                'type'              => 'string'
            ],
            'No index'          => [
                'field_name'        => 'no_index',
                'type'              => 'string'
            ]
        ];
    }

    /**
     * Returns a pre-existing news article record based on URL 
     * if found, or creates a new one if not
     * 
     * @param string $url
     * @return \Modules\News\Models\NewsArticle
     */
    protected function getExistingRecord(string $url): NewsArticle
    {
        $url_settings = $this->getUrlAndRedirect($url);
        $new_url = $url_settings['url'];
        $NewsArticle = $this->getRecordByUrl($new_url);
        if (!$NewsArticle) {
            $NewsArticle = new NewsArticle([
                'news_article_id'   => $this->getNextDbRecordId('news_articles', 'news_article_id'),
                'language_id'       => 1,
                'version'           => 1
            ]);
        }
        return $NewsArticle;
    }

    /**
     * Returns an article URL & redirect from/to paths
     * in a consistent format
     * 
     * @param string $url
     * @return array
     */
    protected function getUrlAndRedirect(string $url): array
    {
        $slug = $this->getUrlSlug($url);
        $new_url = $this->getListingUrlSlug() . '/' . $slug;

        return [
            'url'       => $new_url,
            'redirect'  => [
                'from'      => $this->removeDomainFromUrl($url),
                'to'        => $new_url
            ]
        ];
    }

    /**
     * Returns the URL slug for the news listing page
     * 
     * @return string
     */
    protected function getListingUrlSlug(): string
    {
        if (is_null($this->ListingPage)) {
            $this->ListingPage = Url::getSystemPage('news', 'index');
        }

        $slug = '';
        if ($this->ListingPage) {
            $slug = $this->ListingPage->url_slug;
        }
        if ($this->Category) {
            $slug = $this->Category->url_slug;
        }
        return $slug;
    }

    /**
     * Set the local category property based on the provided
     * category name
     * 
     * @param string $category_name
     * @return void
     */
    protected function setCategory(string $category_name): void
    {
        if (is_null($this->Category)) {
            $Category = NewsCategory::where('title', $category_name)->first();
            if ($Category) {
                $this->Category = $Category;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createNewUrlObject(int $urlable_id): Url
    {
        return new Url([
            'urlable_id'    => $urlable_id,
            'urlable_type'  => NewsArticle::class,
            'language_id'   => 1
        ]);
    }

    /**
     * Find a news author ID based on their name, caching
     * them in the local array property for performance
     * 
     * @param string $author_name
     * @return int
     */
    protected function findAuthorID(string $author_name): int
    {
        $search_key = strtolower(trim($author_name));
        
        if (!isset($this->author_id_map[$search_key])) {
            $Author = NewsAuthor::where('title', 'LIKE', $search_key)->first();
            if ($Author) {
                $this->author_id_map[$search_key] = $Author->news_author_id;
            }
        }

        return isset($this->author_id_map[$search_key]) ? $this->author_id_map[$search_key] : 0;
    }
}