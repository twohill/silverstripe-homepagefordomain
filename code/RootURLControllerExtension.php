<?php


namespace Twohill\HomepageForDomain;


use SilverStripe\Core\Extension;
use SilverStripe\CMS\Model\SiteTree;

class RootURLControllerExtension extends Extension
{

    /**
     * @param $link
     * @return string
     */
    public function updateHomepageLink(&$link)
    {
        $host = str_replace('www.', null, $_SERVER['HTTP_HOST']);
        $candidates = SiteTree::get()->where([
            '"SiteTree"."HomepageForDomain" LIKE ?' => "%$host%"
        ]);

        /** @var SiteTree $candidate */
        foreach ($candidates as $candidate) {
            if (preg_match('/(,|^) *' . preg_quote($host) . ' *(,|$)/', $candidate->HomepageForDomain)) {
                $link = trim($candidate->RelativeLink(true), '/');
            }
        }

        return $link;
    }
}
