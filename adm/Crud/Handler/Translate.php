<?php

namespace Adm\Crud\Handler;

use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class Translate extends Edit
{
    /**
     * Handle the request and load the translate form
     * 
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function handle(): View|RedirectResponse
    {
        $item_class = $this->getConfigProperty('class');
        $Item = $item_class::query()->withoutGlobalScopes()
            ->where([
                $this->getClassIDField()    => $this->item_id,
                'language_id'               => request()->query('lang-to')
            ])
            ->first();
        if ($Item) {
            $this->CrudInterface->setLanguageId(request()->query('lang-to'));
            return response()->redirectTo($this->getCrudUrl($this->getConfigProperty('url_slug'), 'edit', $this->item_id));
        }

        return parent::handle();
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormAction(): string
    {
        return $this->getCrudUrl($this->getConfigProperty('url_slug'), 'translate-store', $this->item_id);
    }

    /**
     * Load the item being translated
     * 
     * @return void
     * @throws \Exception
     */
    protected function validateSetItem(): void
    {
        $item_class = $this->getConfigProperty('class');
        $Item = $item_class::query()->withoutGlobalScopes()
            ->where([
                $this->getClassIDField()    => $this->item_id,
                'language_id'               => $this->getTranslateFromLanguageId()
            ])
            ->first();

        if (!$Item) {
            throw new Exception($this->getConfigProperty('title') . ' not found with ID ' . $this->item_id . ' to translate from');
        }

        if ($this->itemHasVersioning($Item)) {
            $Item = $item_class::query()->withoutGlobalScopes()
                ->where([
                    $this->getClassIDField()    => $this->item_id,
                    'language_id'               => $this->getTranslateFromLanguageId()
                ])
                ->orderBy('version', 'desc')
                ->first();
        }

        $this->CrudInterface->setLanguageId(request()->query('lang-to'));

        $this->processItem($Item);
        $this->Item = $Item;
    }

    /**
     * Return the language ID the translation is from
     * 
     * @return int
     */
    protected function getTranslateFromLanguageId(): int
    {
        return request()->query('from-lang', config('app.default_language_id'));
    }
}
