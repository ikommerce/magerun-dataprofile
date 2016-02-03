<?php

namespace Ikom;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CmsList extends AbstractMagentoCommand{
	
	protected function configure()
	{
		$this
		->setName('ikom:cms:list')
		->addArgument('file', InputArgument::REQUIRED, 'Filename')
		->addArgument('storeid', InputArgument::OPTIONAL, 'Store Id')
		->setDescription('export list cms page')
		;
	}
	
    protected function execute(InputInterface $input, OutputInterface $output)
    {
		$logfile  = 'import_export.log';      // Import/Export log file

		$this->detectMagento($output);
      	if ($this->initMagento()) {
      		$dialog = $this->getHelperSet()->get('dialog');
      		$filename = $input->getArgument('file');
            if ($filename == null) {
                $filename = $dialog->ask($output, '<question>Filename:</question>');
            }
            $storeId = $input->getArgument('storeid');

            if ($storeId != null) {
            	$store = array($storeId);
            	$store[] = \Mage_Core_Model_App::ADMIN_STORE_ID;
            }

            $pageModel = \Mage::getModel('cms/page');
            $collection = $pageModel->getCollection()->getAllIds();
            			#->addFieldToFilter('identifier', $urlkey);
            if ($storeId != null) {
            	$collection = $collection
            		->addStoreFilter($storeId)
            		->addFieldToFilter('store_id', array('in' => $store));
            }
            #$page = $page->getAllIds();
            $intro = 'Lista delle pagine statiche';
            file_put_contents('$filename', $intro);
           	foreach($page as $collection){
	            	$page->load();
	            	$data = json_encode($page->getData(), JSON_PRETTY_PRINT | JSON_FORCE_OBJECT);
	            	$_data = $page->getData(); 
	            	$urlkey =  $_data['identifier'];
		            file_put_contents($filename, $urlkey,FILE_APPEND);
	      			\Mage::log("exported page ${urlkey}", null, $logfile);
      			}
      	}
    }
}