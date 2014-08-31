<?php
class SpecialRandomByNamespace extends SpecialPage {
    public function __construct() {
        parent::__construct('RandomByNamespace');
    }
    public function execute($par) {
        global $wgExtraNamespaces;
        $request = $this->getRequest();
        $output = $this->getOutput();
        $output->setPageTitle($this->msg('randombynamespace')->parse());
        foreach ($wgExtraNamespaces as $id => $ns) {
            $ns = str_replace('_', ' ', $ns);
            $output->addWikiText("*[[Special:Random/$ns|$ns]]\n");
        }
    }
}
