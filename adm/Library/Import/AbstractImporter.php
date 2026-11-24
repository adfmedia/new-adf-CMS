<?php

namespace Adm\Library\Import;

use Adm\Library\Import\Helpers\DbImportHelper;
use Adm\Library\Import\Helpers\FileImportHelper;
use Adm\Library\Import\Helpers\WordPressHelper;
use App\Models\AssignedSizedFile;
use App\Models\Redirect;
use App\Models\Url;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Abstracted base class for website data imports
 */
abstract class AbstractImporter
{
    /**
     * Local instance of the console command used to call
     * the import process
     * 
     * @var ?\Illuminate\Console\Command
     */
    protected $ConsoleCommand       = null;

    /**
     * The internal base URL we're importing content from,
     * used as a basis to replace internal links
     * 
     * @var string
     */
    protected $internal_url_base    = 'https://www.domain.com/';

    /**
     * Regex pattern used to find images within WYSIWYG content,
     * used to import images to the local file manager
     * 
     * @var string
     */
    protected $wysiwyg_img_regex    = '/(https:\/\/www\.domain\.com\/images\/[^"]*)/';

    /**
     * Local variable denoting the label for the import
     * resource type, used as an option when running the
     * import console command
     * 
     * @var string
     */
    public $resource_label       = '';

    /**
     * Sets the provided console command to the import library
     * 
     * @param \Illuminate\Console\Command $Command
     */
    public function setConsoleCommand(Command $Command): void
    {
        $this->ConsoleCommand = $Command;
    }

    /**
     * Import a data file - to be extended by the resource specific library
     * 
     * @return void
     * @throws \Exception
     */
    public function importData(): void
    {
        throw new Exception('importData function not extended.');
    }

    /**
     * Map a source data file to an import-ready data file - to be 
     * extended by the resource specific library
     * 
     * @return void
     * @throws \Exception
     */
    public function mapSourceData(): void
    {
        throw new Exception('mapSourceData function not extended.');
    }

    /**
     * Post-process any mapped source data, ready for import - to be 
     * extended by the resource specific library
     * 
     * @return void
     * @throws \Exception
     */
    public function postProcessMappedSourceData(): void
    {
        throw new Exception('postProcessMappedSourceData function not extended.');
    }

    /**
     * When processing the import data file, map the columns and
     * data to match the import library configuration
     * 
     * @param string $sheet_path
     * @return array
     * @throws \Exception
     */
    protected function parseImportSheet(string $sheet_path): array
    {
        if (!file_exists($sheet_path)) {
            throw new Exception('The specified sheet at ' . $sheet_path . ' does not exist.');
        }

        $sheet_data = [];
        $import_map = $this->getImportFieldConfiguration();
        $heading_indices = array_keys($import_map);

        $headers_read = false;
        $fp = fopen($sheet_path, 'r');
        while ($row = fgetcsv($fp)) {
            if (!$headers_read) {
                $headers_read = true;
                continue;
            }

            $this_row = [];
            foreach ($heading_indices as $col_idx => $heading) {
                $details = $import_map[$heading];
                $default = isset($details['default']) ? $details['default'] : null;

                $row_value = isset($row[$col_idx]) ? $row[$col_idx] : $default;
                switch ($details['type']) {
                    case 'sf_indexability': // Used for no-index flag, so return true if SF shows 'Indexable'
                        $row_value = (strtolower($row_value) != 'indexable');
                        break;
                    case 'wysiwyg':
                        $row_value = $this->parseWysiwygContent($row_value);
                        break;
                    case 'wysiwyg_wpautop':
                        $row_value = $this->parseWysiwygContent(WordPressHelper::wp_auto_p($row_value));
                    case 'textarea':
                        $row_value = str_replace(['<br/>', '<br>'], PHP_EOL, $row_value);
                        $row_value = strip_tags($row_value);
                        break;
                    case 'strip_tags':
                        $row_value = strip_tags($row_value);
                        break;
                    case 'boolean':
                        $row_value = in_array(
                            strtolower($row_value),
                            [1, 'y', 'yes', 'true']
                        );
                        break;
                }

                $this_row[$details['field_name']] = $row_value;
            }

            $sheet_data[] = $this_row;
        }
        fclose($fp);

        return $sheet_data;
    }

    /**
     * Parse WYSIWYG content & find any images requiring import.
     * Optionally strip tags from the content.
     * 
     * @param string $content
     * @param bool $strip_tags
     * @return array
     */
    protected function parseWysiwygContent(string $content, bool $strip_tags = false): array
    {
        $parsed_details = [
            'content'   => $content,
            'images'    => []
        ];

        if (!empty($this->wysiwyg_img_regex)) {
            preg_match_all($this->wysiwyg_img_regex, $parsed_details['content'], $matches);
            if (isset($matches[1]) && !empty($matches[1])) {
                $parsed_details['images'] = $matches[1];
            }
        }

        if ($strip_tags) {
            $parsed_details['content'] = strip_tags(
                $parsed_details['content'],
                ['<h2>', '<h3>', '<h4>', '<h5>', '<h6>', '<p>', '<span>', '<b>', '<strong>', '<em>', '<i>', '<a>']
            );
        }

        return $parsed_details;
    }

    /**
     * Import an external image, mapping it to the provided resource
     * 
     * @param string $image_url                             The image URL to import from
     * @param \Illuminate\Database\Eloquent\Model $Resource The resource model to import to
     * @param string $resource_id_field                     The main ID field of the resource model
     * @param string $field_name                            The image field to import to
     * @param string $img_size                              The image size to import to
     * @param array $img_config                             The image size confirmation to import to
     * @param string $folder_name                           The file manager folder to import the image into
     * @return void
     */
    protected function importImage(
        string $image_url,
        Model $Resource,
        string $resource_id_field,
        string $field_name,
        string $img_size,
        array $img_config,
        string $folder_name
    ): void {
        $Helper = new FileImportHelper();
        $url = $image_url;
        $name = $Helper->getFileNameFromUrl($url);

        $Image = null;
        if (!$Helper->fileExistsInDirectory($name, $folder_name)) {
            try {
                $ext = pathinfo($url, PATHINFO_EXTENSION);

                $storage_name = $Helper->importFileFromUrl($url, $ext);
                if (!empty($storage_name)) {
                    $Image = $Helper->createFileInDirectory($storage_name, $Helper->getFileNameFromUrl($url), $folder_name);
                }
            } catch (Exception $e) {
            }
        } else {
            $Image = $Helper->getFileInDirectory($name, $folder_name);
        }

        if ($Image) {
            AssignedSizedFile::create([
                'morphable_id'      => $Resource->{$resource_id_field},
                'morphable_type'    => get_class($Resource),
                'morphable_field'   => $field_name,
                'language_id'       => $Resource->language_id,
                'version'           => $Resource->version,
                'size'              => $img_size,
                'file_id'           => $Image->id,
                'width'             => $img_config['width'],
                'height'            => $img_config['height'],
                'render_style'      => $img_config['style'],
            ]);

            // Save as array for cast
            $Resource->image = [
                $img_size   => [
                    'file_id'   => $Image->id
                ]
            ];
            $Resource->save();
        }
    }

    /**
     * Imports images into WYSIWYG content, replacing their URLs.
     * Returns the content with URL replacements.
     * 
     * @param string $content       : The WYSIWYG content in question
     * @param array $images         : The images found within the content
     * @param string $folder_name   : The file manager folder to import images into
     * @return string
     */
    protected function importMapWysiwygImages(string $content, array $images, string $folder_name): string
    {
        $Helper = new FileImportHelper();
        foreach ($images as $image_url) {
            $replacement = '';

            $file_name = $Helper->getFileNameFromUrl($image_url);
            if (!$Helper->fileExistsInDirectory($file_name, $folder_name)) {
                $storage_name = $this->importFileFromUrl($image_url);
                if (!empty($storage_name)) {
                    $File = $Helper->createFileInDirectory(
                        $storage_name,
                        $Helper->getFileNameFromUrl($image_url),
                        $folder_name
                    );
                    $replacement = route('image', $File->full_path);
                }
            } else {
                $File = $Helper->getFileInDirectory($file_name, $folder_name);
                $replacement = route('image', $File->full_path);
            }

            $content = str_replace(
                $image_url,
                $replacement,
                $content
            );
        }
        return $content;
    }

    /**
     * Replace any internal links in the content with the current
     * site base URL
     * 
     * @param string $content
     * @return string
     */
    protected function replaceInternalLinks(string $content): string
    {
        return str_replace(
            $this->internal_url_base,
            config('app.url'),
            $content
        );
    }

    /**
     * Returns the final URL slug for the provided URL.
     * e.g. https://www.domain.com/my/url/here will return here
     * 
     * @param string $url
     * @return string
     */
    protected function getUrlSlug(string $url): string
    {
        $path = str_replace($this->internal_url_base, '', $url);
        $parts = explode('/', rtrim($path, '/'));
        return array_pop($parts);
    }

    /**
     * Return the import configuration column mapping for the
     * current data file - to be extended by the resource specific library.
     * 
     * Array format:
     * 'Column in CSV' => [
     *      'field_name'    => 'array_idx_mapped_to',
     *      'type'          => 'field_type' (see parseImportSheet method for custom types)
     * ]
     * 
     * @return array
     * @throws \Exception
     */
    protected function getImportFieldConfiguration(): array
    {
        throw new Exception('getImportFieldConfiguration function not extended.');
    }

    /**
     * Imports a file from the provided URL into the application
     * file storage system, returning the storage file name on success
     * 
     * @param string $file_url
     * @return ?string
     */
    protected function importFileFromUrl(string $file_url): ?string
    {
        if (empty($file_url)) {
            return null;
        }

        $file_url = $this->manipulateImageUrl($file_url);
        $ext = $this->stripQueryString(pathinfo($file_url, PATHINFO_EXTENSION));

        $Helper = new FileImportHelper();
        try {
            return $Helper->importFileFromUrl($file_url, $ext);
        } catch (Exception $e) {
            $this->outputMessage($e->getMessage(), true);
            return null;
        }
    }

    /**
     * Manipulate the provided image URL if required on a per-library basis,
     * e.g. to remove any resize attributes
     * 
     * @param string $image_url
     * @return string
     */
    protected function manipulateImageUrl(string $image_url): string
    {
        return $image_url;
    }

    /**
     * Removes a query string from the provided string
     * 
     * @param string $string
     * @return string
     */
    protected function stripQueryString(string $string): string
    {
        if (stristr($string, '?') !== false) {
            $string = substr($string, 0, stripos($string, '?'));
        }
        return $string;
    }

    /**
     * Return the next 'incremental' ID for a database record when not
     * auto-incrementing
     * 
     * @param string $table_name
     * @param string $id_field
     * @return int
     */
    protected function getNextDbRecordId(string $table_name, string $id_field): int
    {
        $Helper = new DbImportHelper();
        return $Helper->getNextDbRecordId($table_name, $id_field);
    }

    /**
     * Returns a pre-existing resource by it's URL, or null if
     * not found
     * 
     * @param string $url
     * @return ?\Illuminate\Database\Eloquent\Model
     */
    protected function getRecordByUrl(string $url): ?Model
    {
        $Url = Url::where('url', $url)->first();
        return $Url && $Url->urlable ? $Url->urlable : null;
    }

    /**
     * Stores a URL & provided meta against the provided URL object.
     * If no URL object is provided, a new one is created.
     * 
     * @param string $url
     * @param ?string $meta_title
     * @param ?string $meta_description
     * @param bool $no_index
     * @param ?\App\Models\Url $UrlObj
     * @param int $urlable_id
     * @return void
     */
    protected function storeUrl(string $url, ?string $meta_title, ?string $meta_description, bool $no_index, ?Url $UrlObj, int $urlable_id): void
    {
        if (!$UrlObj) {
            $UrlObj = $this->createNewUrlObject($urlable_id);
        }

        $UrlObj->url = $this->removeDomainFromUrl($url);
        $UrlObj->meta_title = $meta_title;
        $UrlObj->meta_description = $meta_description;
        $UrlObj->no_index = $no_index;
        $UrlObj->save();
    }

    /**
     * Creates a redirect from the provided URL, to the provided URL, 
     * with the provided type
     * 
     * @param string $url_from
     * @param string $url_to
     * @param int $type
     * @return void
     */
    protected function createRedirect(string $url_from, string $url_to, int $type = 301): void
    {
        if (rtrim($url_from, '/') == $url_to) {
            return;
        }

        Redirect::updateOrCreate(
            ['old_url'  => $url_from],
            [
                'new_url'       => $url_to,
                'redirect_type' => $type,
                'status'        => true
            ]
        );
    }

    /**
     * Creates a new URL object - to be extended by the resource specific
     * library
     * 
     * @param int $urlable_id
     * @return \App\Models\Url
     * @throws \Exception
     */
    protected function createNewUrlObject(int $urlable_id): Url
    {
        throw new Exception('createNewUrlObject function not extended.');
    }

    /**
     * Returns the path part of the provided URL
     * 
     * @param string $url
     * @return string
     */
    protected function removeDomainFromUrl(string $url): string
    {
        return trim(parse_url($url, PHP_URL_PATH), '/');
    }

    /**
     * Maps the provided Screaming Frog (CSV) export file to the
     * provided columns
     * 
     * @param string $sheet_path
     * @param array $column_map
     * @return array
     * @throws \Exception
     */
    protected function mapScreamingFrogSheet(string $sheet_path, array $column_map): array
    {
        $sheet_data = [];

        if (!file_exists($sheet_path)) {
            throw new Exception('The specified sheet at ' . $sheet_path . ' does not exist.');
        }

        $sheet_headers = [];
        foreach ($column_map as $mapped_heading) {
            $sheet_headers[] = $mapped_heading;
        }
        $sheet_data[] = $sheet_headers;

        $fp = fopen($sheet_path, 'r');
        $headers_read = false;
        while ($row = fgetcsv($fp)) {
            if (!$headers_read) {
                $headers_read = true;
                continue;
            }

            $sheet_row = [];
            foreach ($column_map as $col_idx => $mapped_heading) {
                $sheet_row[$mapped_heading] = isset($row[$col_idx]) ? trim($row[$col_idx]) : '';
            }

            $sheet_data[] = $sheet_row;
        }
        fclose($fp);

        return $sheet_data;
    }

    /**
     * Maps the provided WordPress (XML) export file to the
     * provided columns
     * 
     * @param string $xml_path
     * @param array $column_map
     * @return array
     * @throws \Exception
     */
    protected function mapWordpressXMLExport(string $xml_path, array $column_map): array
    {
        $sheet_data = [];

        if (!file_exists($xml_path)) {
            throw new Exception('The specified XML at ' . $xml_path . ' does not exist.');
        }

        $sheet_headers = [];
        foreach ($column_map as $mapped_heading) {
            $sheet_headers[] = $mapped_heading;
        }
        $sheet_data[] = $sheet_headers;

        $XML = simplexml_load_file($xml_path);
        if ($XML === false) {
            throw new Exception('Failed to load XML at ' . $xml_path . '.');
        }

        foreach ($XML->channel->item as $Item) {
            $sheet_row = [];
            foreach ($column_map as $xml_element => $mapped_heading) {
                // Ensure the CSV column is present
                $sheet_row[$mapped_heading] = '';
                if (stripos($xml_element, 'postmeta|') === 0) {
                    $key = str_replace('postmeta|', '', $xml_element);
                    foreach ($Item->children('wp', true)->postmeta as $MetaElement) {
                        if ((string)$MetaElement->children('wp', true)->meta_key == $key) {
                            $sheet_row[$mapped_heading] = (string)$MetaElement->children('wp', true)->meta_value;
                        }
                    }
                } elseif (stripos($xml_element, '[') !== false) {
                    $parts = explode('[', $xml_element);
                    $node = $parts[0];
                    $attr = $parts[1];

                    $attr = rtrim($attr, ']');
                    $attr_parts = explode('=', $attr);
                    $attr_name = $attr_parts[0];
                    $attr_val = str_replace('"', '', $attr_parts[1]);

                    foreach ($Item->{$node} as $InnerNode) {
                        foreach ($InnerNode->attributes() as $name => $val) {
                            if ($name == $attr_name && $val == $attr_val) {
                                $sheet_row[$mapped_heading] = (string)$InnerNode;
                            }
                        }
                    }
                } elseif (stripos($xml_element, ':') !== false) {
                    $parts = explode(':', $xml_element);
                    if (sizeof($parts) == 2) {
                        $sheet_row[$mapped_heading] = (string)$Item->children($parts[0], true)->{$parts[1]};
                    }
                } else {
                    $sheet_row[$mapped_heading] = (string)$Item->{$xml_element};
                }
            }

            $sheet_data[] = $sheet_row;
        }

        return $sheet_data;
    }

    /**
     * Output a message to the library's console command
     * 
     * @param string $message
     * @param bool $error
     */
    protected function outputMessage(string $message, bool $error = false): void
    {
        if ($this->ConsoleCommand) {
            if ($error) {
                $this->ConsoleCommand->error($message);
            } else {
                $this->ConsoleCommand->line($message);
            }
        }
    }

    /**
     * Log a message to the internal log files
     * 
     * @param mixed $message
     * @param bool $error
     */
    protected function logMessage(mixed $message, bool $error = false): void
    {
        if ($error) {
            Log::error($message);
        } else {
            Log::debug($message);
        }
    }

    /**
     * Output & log a message, using the above 2 methods
     * 
     * @param string $message
     * @param bool $error
     */
    protected function outputLogMessage(string $message, bool $error = false): void
    {
        $this->outputMessage($message, $error);
        $this->logMessage($message, $error);
    }
}
