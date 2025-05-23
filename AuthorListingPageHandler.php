<?php

namespace APP\plugins\generic\authorListing;

use APP\core\Request;
use APP\handler\Handler;
use APP\search\ArticleSearch;
use APP\template\TemplateManager;
use Illuminate\Support\Facades\DB;
use PKP\controllers\page\PageHandler;
use PKP\security\authorization\ContextAccessPolicy;
use PKP\security\authorization\ContextRequiredPolicy;
use PKP\security\authorization\PKPSiteAccessPolicy;

class AuthorListingPageHandler extends Handler {

    public AuthorListingPlugin $plugin;
    public string $author;

    public function __construct(AuthorListingPlugin $plugin)
    {
        parent::__construct();

        $this->plugin = $plugin;
    }

    /**
     * @see PKPHandler::initialize()
     *
     * @param \APP\core\Request $request
     * @param array $args Arguments list
     */
    public function initialize($request, $args = [])
    {
        $urlPath = empty($args) ? 0 : array_shift($args);

        $this->author = str_replace('+', ' ', $urlPath);
    }

    public function view($args, $request)
    {

        $templateMgr = TemplateManager::getManager($request);
        $context = $request->getContext();

        $article_raw = DB::select('
            SELECT s.submission_id FROM (
                SELECT CONCAT( as3.setting_value, " ", as2.setting_value ) AS `computedName`, authors.publication_id
                FROM `authors`
                INNER JOIN `author_settings` as2 ON `authors`.`author_id` = as2.author_id AND as2.setting_name = "familyName"
                INNER JOIN `author_settings` as3 ON `authors`.author_id = as3.author_id AND as3.setting_name = "givenName"
            ) al
            INNER JOIN publications p ON p.publication_id = al.publication_id
            INNER JOIN submissions s ON s.submission_id = p.submission_id
            WHERE al.computedName = ? AND s.context_id = ?
        ', [ $this->author, $context->getId() ]);
        $article_ids = [];
        foreach($article_raw as $a) {
            $article_ids[] = $a->submission_id;
        }

        $articleSearch = new ArticleSearch();
        $articles = $articleSearch->formatResults($article_ids);
        $templateMgr->assign('results', $articles);
        $templateMgr->assign('author', $this->author);

        return $templateMgr->display(
            $this->plugin->getTemplateResource(
                'listArticles.tpl'
            )
        );

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

        if(@$_GET['_show_data']) { // TODO: check if admin or not to show this??
            echo '<p>Debug level data for author listing:</p>';
            echo '<table><tr><th>Author Hash</th><th>GiveName</th><th>FamilyName</th><th>Affiliation</th><th>Associated Articles</th></tr>';
            foreach($authors as $author) {
                echo '<tr><td>' . $author->unique . '</td><td>' . $author->givenName . '</td><td>' . $author->familyName . '</td><td>' . $author->affiliation . '</td>';
                echo '<td>';
                // Lookup articles assigned to
                $data = DB::select('
                    SELECT * FROM authors
                    INNER JOIN publications ON authors.publication_id = publications.publication_id
                    WHERE email = ? AND publications.status = 3
                ', [ $author->email ]);
                foreach($data as $item) {
                    echo '<a href="../article/view/' . $item->submission_id . '">' . $item->submission_id . '</a> ';
                }
                echo '</td>';
                echo '</tr>';
            }
            echo '</tr></table>';
        }

        $authors = array_map(function($item) {
            $item->familyName = trim($item->familyName);
            $item->givenName = trim($item->givenName);
            return $item;
        }, $authors);
        $authors = array_filter($authors, function($item) {
            return strlen($item->familyName) > 0 && strlen($item->givenName) > 0;
        });

        $templateMgr->assign('authors', $authors);

        return $templateMgr->display(
            $this->plugin->getTemplateResource(
                'listAuthors.tpl'
            )
        );
    }

}