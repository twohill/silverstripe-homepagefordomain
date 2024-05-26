<?php


namespace Twohill\HomepageForDomain;

use SilverStripe\Core\Extension;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\DB;

class RootURLControllerExtension extends Extension
{

    /**
     * @param $link
     * @return string
     */
    public function updateHomepageLink(&$link)
    {

        // Evaluate only when $_SERVER['HTTP_HOST'] is defined, i.e. skip CLI where there's no HTTP_HOST set
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = str_replace('www.', '', $_SERVER['HTTP_HOST']);

            // Check for a specific db column existence for cases prior to the column being established (dev/build etc.)
            $columnExists = (bool) DB::query('SHOW columns from "SiteTree" WHERE "field" = \'HomepageForDomain\'')
                ->numRecords();
            if ($columnExists) {
                $candidates = SiteTree::get()->where([
                    '"SiteTree"."HomepageForDomain" LIKE ?' => "%$host%"
                ]);
                if ($candidates) {
                    /** @var SiteTree $candidate */
                    foreach ($candidates as $candidate) {
                        if (preg_match('/(,|^) *' . preg_quote($host) . ' *(,|$)/', $candidate->HomepageForDomain)) {
                            $link = trim($candidate->RelativeLink(true), '/');
                        }
                    }
                }
            }
        }
        return $link;
    }
}
