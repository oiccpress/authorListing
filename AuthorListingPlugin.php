<?php

/**
 * Main class for author listing plugin
 * 
 * @author Joe Simpson
 * 
 * @class AuthorListingPlugin
 *
 * @ingroup plugins_generic_authorListing
 *
 * @brief Author Listing
 */

namespace APP\plugins\generic\authorListing;

use APP\core\Application;
use Illuminate\Support\Facades\DB;
use PKP\core\PKPApplication;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

class AuthorListingPlugin extends GenericPlugin {

    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path);

        if ($success && $this->getEnabled()) {
            Hook::add( 'Templates::Admin::Index::AdminFunctions', [$this, 'regenerate'] );
            Hook::add('LoadHandler', [$this, 'setPageHandler']);
            Hook::add('NavigationMenus::itemTypes', [$this, 'menuItemType']);
            Hook::add('NavigationMenus::displaySettings', [ $this, 'menuDisplaySettings' ]);
        }

        return $success;
    }

    public function menuDisplaySettings($hookName, $args)
    {
        $request = Application::get()->getRequest();
        $dispatcher = $request->getDispatcher();
        $menuItem = $args[0];
        if($menuItem->getType() == 'NMI_AUTHOR_LISTING') {
            $menuItem->setUrl($dispatcher->url(
                $request,
                PKPApplication::ROUTE_PAGE,
                null,
                'authors',
                null,
                null
            ));
        }
    }

    public function menuItemType($hookName, $args)
    {
        $itemTypes = &$args[0];
        $itemTypes['NMI_AUTHOR_LISTING'] = [
            'title' => 'Author Listing',
            'description' => 'Author Listing',
        ];
    }

    /**
     * Provide a name for this plugin
     *
     * The name will appear in the Plugin Gallery where editors can
     * install, enable and disable plugins.
     */
    public function getDisplayName()
    {
        return 'Author Listing';
    }

    /**
     * Provide a description for this plugin
     *
     * The description will appear in the Plugin Gallery where editors can
     * install, enable and disable plugins.
     */
    public function getDescription()
    {
        return 'This plugin provides an effecient author listing page.';
    }

    public function setPageHandler(string $hookName, array $args): bool
    {
        $page =& $args[0];
        $handler =& $args[3];
        if ($this->getEnabled() && $page === 'authors') {
            $handler = new AuthorListingPageHandler($this);
            return true;
        }
        return false;
    }

    public function regenerate() {

        // This table is purely for storing a cache of unique authors
        $sql = "
            CREATE TABLE IF NOT EXISTS `journal_authors` (
                `author_id` BIGINT(20) NOT NULL ,
                `unique` VARCHAR(100) NOT NULL ,
                `context_id` BIGINT(20) NOT NULL
            ) ENGINE = InnoDB; 
        ";
        DB::affectingStatement($sql);

        // Wipe it if already exists
        DB::affectingStatement("TRUNCATE `journal_authors`");

        // With a single DB query populate this table with all of the relevant data
        // Essentially we use `unique` as either ORCID or email (whatever is available) as an SHA1 purely to
        // have some sense of a "unique author" to display
        $sql = "
            INSERT INTO `journal_authors` (`unique`, `context_id`, `author_id`)
            SELECT SHA1(`unique`) AS `unique`, MIN(`context_id`) AS context_id, MIN(`author_id`) AS author_id FROM (
                SELECT IFNULL(  `author_settings`.`setting_value`, `authors`.`email` ) AS `unique`, `submissions`.`context_id`, MIN(`authors`.`author_id`) AS author_id FROM `authors`
                INNER JOIN `publications` ON `publications`.`publication_id` = `authors`.`publication_id`
                INNER JOIN `submissions` ON `submissions`.`current_publication_id` = `publications`.`publication_id`
                LEFT OUTER JOIN `author_settings` ON `author_settings`.`author_id` = `authors`.`author_id` AND `author_settings`.`setting_name` = 'orcid'
                GROUP BY email, context_id, `author_settings`.`setting_value`
            ) authorData
            GROUP BY `authorData`.`unique`;
        ";
        DB::affectingStatement($sql);

    }
    
}
