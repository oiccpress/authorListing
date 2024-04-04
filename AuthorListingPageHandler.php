<?php

namespace APP\plugins\generic\authorListing;

use APP\core\Request;
use APP\handler\Handler;
use APP\template\TemplateManager;
use PKP\controllers\page\PageHandler;
use PKP\security\authorization\ContextAccessPolicy;
use PKP\security\authorization\ContextRequiredPolicy;
use PKP\security\authorization\PKPSiteAccessPolicy;

class AuthorListingPageHandler extends Handler {

    public AuthorListingPlugin $plugin;

    public function __construct(AuthorListingPlugin $plugin)
    {
        parent::__construct();

        $this->plugin = $plugin;
    }



    public function index($args, $request)
    {
        $templateMgr = TemplateManager::getManager($request);

        return $templateMgr->display(
            $this->plugin->getTemplateResource(
                'listAuthors.tpl'
            )
        );
    }

}