<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class CommandCategoryRelationRepository extends ServiceEntityRepository
{

    /**
     * Export
     * 
     * @param int $pollerId
     * @return array
     */
    public function export(int $pollerId): array
    {
        $sql = <<<SQL
SELECT
    ccr1.*
FROM command AS t1
INNER JOIN command_categories_relation AS ccr1 ON ccr1.command_command_id = t1.command_id
INNER JOIN cfg_nagios AS cn1 ON
    cn1.global_service_event_handler = t1.command_id OR
    cn1.global_host_event_handler = t1.command_id OR
    cn1.ocsp_command = t1.command_id OR
    cn1.ochp_command = t1.command_id OR
    cn1.host_perfdata_command = t1.command_id OR
    cn1.service_perfdata_command = t1.command_id OR
    cn1.host_perfdata_file_processing_command = t1.command_id OR
    cn1.service_perfdata_file_processing_command = t1.command_id
WHERE
    cn1.nagios_id = :id
GROUP BY ccr1.cmd_cat_id

UNION

SELECT
    ccr2.*
FROM command AS t2
INNER JOIN command_categories_relation AS ccr2 ON ccr2.command_command_id = t2.connector_id
INNER JOIN poller_command_relations AS pcr2 ON pcr2.command_id = t2.command_id
WHERE
    pcr2.poller_id = :id
GROUP BY ccr2.cmd_cat_id

UNION

SELECT
    ccr3.*
FROM command AS t3
INNER JOIN command_categories_relation AS ccr3 ON ccr3.command_command_id = t3.connector_id
INNER JOIN command_categories AS cc3 ON cc3.cmd_category_id = ccr3.category_id
INNER JOIN host AS h3 ON
    h3.command_command_id = t3.command_id OR
    h3.command_command_id2 = t3.command_id
INNER JOIN ns_host_relation AS nhr3 ON nhr3.host_host_id = h3.host_id
WHERE
    nhr3.nagios_server_id = :id
GROUP BY ccr3.cmd_cat_id

UNION

SELECT
    ccr4.*
FROM command AS t4
INNER JOIN command_categories_relation AS ccr4 ON ccr4.command_command_id = t4.connector_id
INNER JOIN host AS h4 ON
    h4.command_command_id = t4.command_id OR
    h4.command_command_id2 = t4.command_id
INNER JOIN ns_host_relation AS nhr4 ON nhr4.host_host_id = h4.host_id
WHERE
    nhr4.nagios_server_id = :id
GROUP BY ccr4.cmd_cat_id

UNION

SELECT
    ccr.*
FROM command AS t
INNER JOIN command_categories_relation AS ccr ON ccr.command_command_id = t.connector_id
INNER JOIN service AS s ON
    s.command_command_id = t.command_id OR
    s.command_command_id2 = t.command_id
INNER JOIN host_service_relation AS hsr ON
    hsr.service_service_id = s.service_id
LEFT JOIN hostgroup_relation AS hgr ON hgr.hostgroup_hg_id = hsr.hostgroup_hg_id
LEFT JOIN ns_host_relation AS nhr ON
	nhr.host_host_id = hsr.host_host_id OR
	nhr.host_host_id = hgr.host_host_id
WHERE
    nhr.nagios_server_id = :id
GROUP BY ccr.cmd_cat_id
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $pollerId, PDO::PARAM_INT);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }
}
