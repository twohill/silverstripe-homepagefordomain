<?php

if (!class_exists('SS_Object')) {
    class_alias('Object', 'SS_Object');
}

SS_Object::add_extension('SiteTree', 'HomepageForDomainExtension');
