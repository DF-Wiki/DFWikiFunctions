<?php
class SpecialRandomByNamespace extends SpecialPage {
    public function __construct() {
        parent::__construct('RandomByNamespace');
    }
    public function execute($par) {
        $namespaces = MWNamespace::getCanonicalNamespaces();
        $request = $this->getRequest();
        $output = $this->getOutput();
        $output->addModules('ext.RandomByNamespace');
        $output->setPageTitle($this->msg('randombynamespace')->parse());
        $output->addHTML('<label><input type="checkbox" id="randombynamespace-toggle-ids"> '
            . $this->msg('randombynamespace-toggle-ids')->parse() . '</label>');
        $output->addHTML('<div class="randombynamespace-list">');
        foreach ($namespaces as $id => $ns) {
            if ($id < 0)
                continue;
            if ($ns == '')
                $ns = 'Main';
            $ns = str_replace('_', ' ', $ns);
            $output->addWikiTextAsContent("*[[Special:Random/$ns|$ns]] <span data-id='$id'></span>\n");
        }
        $output->addHTML('</div>');
    }
}
