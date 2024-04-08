<?php

namespace APP\plugins\generic\authorListing;

use APP\core\Request;
use APP\handler\Handler;
use APP\template\TemplateManager;
use Illuminate\Support\Facades\DB;
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

        $context = $request->getContext();

        // Reasonably simple query to collect all of the data we're interested in
        $authors = DB::select('
            SELECT ja.*, a.*, as1.setting_value AS affiliation, as2.setting_value AS familyName, as3.setting_value AS givenName,
                SUBSTRING(as2.setting_value, 1, 1) AS `letter`
            FROM journal_authors ja
            INNER JOIN authors a ON a.author_id = ja.author_id
            INNER JOIN author_settings as1 ON as1.author_id = a.author_id AND as1.setting_name = "affiliation"
            INNER JOIN author_settings as2 ON as2.author_id = a.author_id AND as2.setting_name = "familyName"
            INNER JOIN author_settings as3 ON as3.author_id = a.author_id AND as3.setting_name = "givenName"
            WHERE context_id = ?
            ORDER BY familyName, givenName ASC    
        ', [ $context->getId() ]);
        $templateMgr->assign('authors', $authors);

        return $templateMgr->display(
            $this->plugin->getTemplateResource(
                'listAuthors.tpl'
            )
        );
    }

}