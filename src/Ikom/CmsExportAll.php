<?php

namespace Ikom;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CmsExportAll extends AbstractMagentoCommand{
	
	protected function configure()
	{
		$this
		->setName('ikom:cms:exportall')
		->addArgument('percorso', InputArgument::REQUIRED, 'inserisci il percorso della cartella dove si devono salvare le pagine statiche')
		->addArgument('storeid', InputArgument::OPTIONAL, 'Store Id')
		->setDescription('export all cms page')
		;
	}
	
    protected function execute(InputInterface $input, OutputInterface $output)
    {
		$logfile  = 'import_export.log';      // Import/Export log file

		$this->detectMagento($output);
      	if ($this->initMagento()) {
      		$dialog = $this->getHelperSet()->get('dialog');
      		$percorso = $input->getArgument('percorso');
            if ($percorso == null) {
                $percorso = $dialog->ask($output, '<question>Percorso:</question>');
            }
            $storeId = $input->getArgument('storeid');

            if ($storeId != null) {
            	$store = array($storeId);
            	$store[] = \Mage_Core_Model_App::ADMIN_STORE_ID;
            }

            $pageModel = \Mage::getModel('cms/page');
            $collection = $pageModel->getCollection();
            if ($storeId != null) {
            	$collection = $collection
            		->addStoreFilter($storeId)
            		->addFieldToFilter('store_id', array('in' => $store));
            }
           	foreach($collection as $page){
	            	$page->load();
	            	$data = json_encode($page->getData(), JSON_PRETTY_PRINT | JSON_FORCE_OBJECT);
	            	$_data = $page->getData(); 
	            	$title = $_data['title'];
	            	$urlkey =  $_data['identifier'];
	            	$filename = $percorso.$urlkey;
		            file_put_contents($filename, $data);
	      			\Mage::log("exported page ${title}", null, $logfile);
      			}
      	}
    }
}