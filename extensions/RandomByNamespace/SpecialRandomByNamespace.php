<?php
class SpecialRandomByNamespace extends SpecialPage {
    public function __construct() {
        parent::__construct('RandomByNamespace');
    }
    public function execute($par) {
        $namespaces = MWNamespace::getCanonicalNamespaces();
        $request = $this->getRequest();
        $output = $this->getOutput();
        $output->setPageTitle($this->msg('randombynamespace')->parse());
        foreach ($namespaces as $id => $ns) {
            if ($id < 0)
                continue;
            if ($ns == '')
                $ns = 'Main';
            $ns = str_replace('_', ' ', $ns);
            $output->addWikiText("*[[Special:Random/$ns|$ns]]\n");
        }
    }
}
