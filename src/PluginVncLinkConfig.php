<?php

namespace GlpiPlugin\VncLink;

use CommonDBTM;

class PluginVncLinkConfig extends CommonDBTM
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

    private static function getConfigValues(): array
    {
        global $DB;

        $table = self::getTable();

        $query = <<<SQL
          SELECT name, value from $table
SQL;

        $results = iterator_to_array($DB->query($query));

        foreach($results as $id => $result) {
            $results[$result['name']] = $result['value'];
            unset($results[$id]);
        }
        return $results;
    }

    public static function updateConfigValues(array $values): bool
    {
        global $DB;

        $table = self::getTable();
        $fields = self::getConfigValues();

        foreach (array_keys($fields) as $key) {
            $query = <<<SQL
              UPDATE $table
              SET value='{$values[$key]}'
              WHERE name='{$key}'
SQL;
            $DB->query($query);
        }
        return true;
    }

    public function showConfigForm(): void
    {
        echo "VNC_LINK CONFIG FORM";
    }
}
