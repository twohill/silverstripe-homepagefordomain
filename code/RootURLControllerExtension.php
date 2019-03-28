<?php

namespace Twohill\HomepageForDomain;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Extension;

class RootURLControllerExtension extends Extension
{

    /**
     * Get the full form (e.g. /home/) relative link to the home page for the current HTTP_HOST value. Note that the
     * link is trimmed of leading and trailing slashes before returning to ensure consistency.
     *
     * @param string $link
     */
    public function updateHomePageLink(&$link)
    {
        $host = str_replace('www.', null, $_SERVER['HTTP_HOST']);
        $candidates = SiteTree::get()->where(array(
            '"SiteTree"."HomepageForDomain" LIKE ?' => "%$host%"
        ));
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
