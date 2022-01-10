<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;

class CalculateConversionPageRate extends BaseFilter
{
    /**
     * Constructor.
     *
     * @param DataTable $table The table to eventually filter.
     */
    public function __construct($table)
    {
        parent::__construct($table);
    }

    /**
     * @param DataTable $table
     */
    public function filter($table)
    {
        $formatter = new Formatter();
        $goalTotals = [];

        // Get all goal ids for the table
        $goals = [];
        foreach ($this->getGoalsInTable($table) as $g) {
            $goals[] = $g;
            $goalTotals[$g] = 0;
        }

        // Find all conversions for the table and store in an array
        foreach ($table->getRowsWithoutSummaryRow() as $row) {
            foreach ($goals as $goalId) {
                if (isset($row['goal_'.$goalId.'_nb_conversions_attrib'])) {
                    $goalTotals[$goalId] += $row['goal_'.$goalId.'_nb_conversions_attrib'];
                }
            }
        }

        // Walk the rows and populate the goal_[x]_nb_conversions_page_rate with goal_[x]_nb_conversions / $goalTotals[goal id]
        foreach ($table->getRowsWithoutSummaryRow() as $row) {
            foreach ($goals as $goalId) {
                if (isset($row['goal_'.$goalId.'_nb_conversions_page_uniq'])) {
                    $row['goal_'.$goalId.'_nb_conversions_page_rate'] =
                            Piwik::getQuotientSafe($row['goal_'.$goalId.'_nb_conversions_page_uniq'],
                            $goalTotals[$goalId], 3);
                }
            }
        }
    }

    private function getGoalsInTable(DataTable $table)
    {
        $result = array();
        foreach ($table->getRows() as $row) {
            $goals = $row->getMetadata('goals');
            if (!$goals) {
                continue;
            }

            foreach ($goals as $goalId) {
                $result[] = $goalId;
            }
        }
        return array_unique($result);
    }
}
