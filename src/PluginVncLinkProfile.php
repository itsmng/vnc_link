<?php

namespace GlpiPlugin\VncLink;

use CommonDBTM;
use CommonGLPI;
use Html;
use Profile;
use ProfileRight;
use Session;

class PluginVncLinkProfile extends CommonDBTM
{
    public static function install(): bool
    {
        global $DB;

        $table = self::getTable();

        if (!$DB->tableExists($table)) {
            $query = <<<SQL
              CREATE TABLE `$table` (
                  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'RELATION to glpi_profiles (id)' ,
                  `name` VARCHAR(255) collate utf8_unicode_ci NOT NULL,
                  `value` TEXT collate utf8_unicode_ci default NULL,
                  PRIMARY KEY (`id`)
              ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
            SQL;

            $DB->queryOrDie($query, $DB->error());
        }

        return true;
    }

    public static function uninstall(): bool
    {
        global $DB;

        $table = self::getTable();

        if ($DB->tableExists($table)) {
            $query = <<<SQL
              DROP TABLE `$table`
            SQL;

            $DB->queryOrDie($query, $DB->error());
        }

        return true;
    }

    public static function canCreate(): bool
    {
        if (isset($_SESSION["profile"])) {
            return ($_SESSION["profile"]['pluginvnc_link'] == 'w');
        }
        return false;
    }

    public static function canView(): bool
    {
        if (isset($_SESSION["profile"])) {
            return ($_SESSION["profile"]['pluginvnc_link'] == 'w' || $_SESSION["profile"]['pluginvnc_link'] == 'r');
        }
        return false;
    }

    public static function createAdminAccess($ID): void
    {
        $myProf = new self();
        if (!$myProf->getFromDB($ID)) {
            $myProf->add(array('id' => $ID, 'right' => 'w'));
        }
    }

    public static function addDefaultProfileInfos($profiles_id, $rights): void
    {
        $profileRight = new ProfileRight();

        foreach ($rights as $right => $value) {
            if (!countElementsInTable('glpi_profilerights', ['profiles_id' => $profiles_id, 'name' => $right])) {
                $myright['profiles_id'] = $profiles_id;
                $myright['name']        = $right;
                $myright['rights']      = $value;

                $profileRight->add($myright);

                $_SESSION['glpiactiveprofile'][$right] = $value;
            }
        }
    }

    public static function changeProfile(): void
    {
        $prof = new self();

        if ($prof->getFromDB($_SESSION['glpiactiveprofile']['id'])) {
            $_SESSION["glpi_plugin_vnc_link_profile"] = $prof->fields;
        } else {
            unset($_SESSION["glpi_plugin_vnc_link_profile"]);
        }
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0): string
    {
        if (Session::haveRight("profile", UPDATE) && $item->getType() == 'Profile') {
            return __('Vnc_link', 'vnc_link');
        }

        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0): bool
    {
        if ($item->getType() == 'Profile') {

            $ID = $item->getID();
            $prof = new self();

            foreach (self::getRightsGeneral() as $right) {
                self::addDefaultProfileInfos($ID, [$right['field'] => 0]);
            }

            $prof->showForm($ID);
        }

        return true;
    }

    public static function getRightsGeneral(): array
    {
        $rights = [
            [
                'itemtype'  => self::class,
                'label'     => __('Config update', 'vnc_link'),
                'field'     => 'plugin_vnc_link_config',
                'rights'    => [UPDATE => __('Allow editing', 'vnc_link')],
                'default'   => 23
            ]
        ];

        return $rights;
    }

    public function showForm($profiles_id = 0, $openform = true, $closeform = true): void
    {
        if (!Session::haveRight("profile", READ)) {
            return;
        }

        echo "<div class='firstbloc'>";

        if (($canedit = Session::haveRight('profile', UPDATE)) && $openform) {
            $profile = new Profile();
            echo "<form method='post' action='".$profile->getFormURL()."'>";
        }

        $profile = new Profile();
        $profile->getFromDB($profiles_id);
        $rights = $this->getRightsGeneral();
        $profile->displayRightsChoiceMatrix($rights, ['default_class' => 'tab_bg_2', 'title' => __('General')]);

        if ($canedit && $closeform) {
            echo "<div class='center'>";
            echo Html::hidden('id', ['value' => $profiles_id]);
            echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
            echo "</div>\n";
            Html::closeForm();
        }

        echo "</div>";
    }
}
