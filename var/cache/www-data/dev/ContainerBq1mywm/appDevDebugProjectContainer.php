<?php

namespace ContainerBq1mywm;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;

/**
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
class appDevDebugProjectContainer extends Container
{
    private $buildParameters;
    private $containerDir;
    private $parameters = [];
    private $targetDirs = [];

    public function __construct(array $buildParameters = [], $containerDir = __DIR__)
    {
        $dir = $this->targetDirs[0] = \dirname($containerDir);
        for ($i = 1; $i <= 5; ++$i) {
            $this->targetDirs[$i] = $dir = \dirname($dir);
        }
        $this->buildParameters = $buildParameters;
        $this->containerDir = $containerDir;
        $this->parameters = $this->getDefaultParameters();

        $this->services = [];
        $this->normalizedIds = [
            'autowired.rialto\\accounting\\bank\\transfer\\banktransfer' => 'autowired.Rialto\\Accounting\\Bank\\Transfer\\BankTransfer',
            'autowired.rialto\\purchasing\\invoice\\supplierinvoice' => 'autowired.Rialto\\Purchasing\\Invoice\\SupplierInvoice',
            'autowired.rialto\\purchasing\\invoice\\supplierinvoiceitem' => 'autowired.Rialto\\Purchasing\\Invoice\\SupplierInvoiceItem',
            'autowired.rialto\\purchasing\\supplier\\supplier' => 'autowired.Rialto\\Purchasing\\Supplier\\Supplier',
            'autowired.rialto\\stock\\item\\manufacturedstockitem' => 'autowired.Rialto\\Stock\\Item\\ManufacturedStockItem',
            'autowired.rialto\\stock\\item\\purchasedstockitem' => 'autowired.Rialto\\Stock\\Item\\PurchasedStockItem',
            'doctrine\\common\\persistence\\objectmanager' => 'Doctrine\\Common\\Persistence\\ObjectManager',
            'doctrine\\dbal\\connection' => 'Doctrine\\DBAL\\Connection',
            'doctrine\\orm\\entitymanagerinterface' => 'Doctrine\\ORM\\EntityManagerInterface',
            'fabiang\\xmpp\\client' => 'Fabiang\\Xmpp\\Client',
            'fos\\restbundle\\request\\paramfetcherinterface' => 'FOS\\RestBundle\\Request\\ParamFetcherInterface',
            'fos\\restbundle\\view\\viewhandlerinterface' => 'FOS\\RestBundle\\View\\ViewHandlerInterface',
            'gumstix\\geographybundle\\twig\\geographyextension' => 'Gumstix\\GeographyBundle\\Twig\\GeographyExtension',
            'gumstix\\restbundle\\handler\\accessdeniedhandler' => 'Gumstix\\RestBundle\\Handler\\AccessDeniedHandler',
            'gumstix\\restbundle\\serializer\\formerrornormalizer' => 'Gumstix\\RestBundle\\Serializer\\FormErrorNormalizer',
            'gumstix\\sso\\service\\credentialstorage' => 'Gumstix\\SSO\\Service\\CredentialStorage',
            'gumstix\\sso\\service\\router' => 'Gumstix\\SSO\\Service\\Router',
            'gumstix\\sso\\service\\singlesignon' => 'Gumstix\\SSO\\Service\\SingleSignOn',
            'gumstix\\ssobundle\\security\\cookieauthenticator' => 'Gumstix\\SSOBundle\\Security\\CookieAuthenticator',
            'gumstix\\ssobundle\\security\\headerauthenticator' => 'Gumstix\\SSOBundle\\Security\\HeaderAuthenticator',
            'gumstix\\ssobundle\\security\\loginauthenticator' => 'Gumstix\\SSOBundle\\Security\\LoginAuthenticator',
            'gumstix\\ssobundle\\service\\httpclientfactory' => 'Gumstix\\SSOBundle\\Service\\HttpClientFactory',
            'gumstix\\ssobundle\\service\\logoutservice' => 'Gumstix\\SSOBundle\\Service\\LogoutService',
            'gumstix\\ssobundle\\service\\singlesignonfactory' => 'Gumstix\\SSOBundle\\Service\\SingleSignOnFactory',
            'gumstix\\ssobundle\\twig\\ssoextension' => 'Gumstix\\SSOBundle\\Twig\\SSOExtension',
            'gumstix\\storage\\filestorage' => 'Gumstix\\Storage\\FileStorage',
            'jms\\serializer\\serializerinterface' => 'JMS\\Serializer\\SerializerInterface',
            'league\\tactician\\commandbus' => 'League\\Tactician\\CommandBus',
            'mongodb\\database' => 'MongoDB\\Database',
            'rialto\\accounting\\bank\\account\\availablechequenumbervalidator' => 'Rialto\\Accounting\\Bank\\Account\\AvailableChequeNumberValidator',
            'rialto\\accounting\\bank\\account\\repository\\bankaccountrepository' => 'Rialto\\Accounting\\Bank\\Account\\Repository\\BankAccountRepository',
            'rialto\\accounting\\bank\\transfer\\banktransfercontroller' => 'Rialto\\Accounting\\Bank\\Transfer\\BankTransferController',
            'rialto\\accounting\\debtor\\debtortransactionfactory' => 'Rialto\\Accounting\\Debtor\\DebtorTransactionFactory',
            'rialto\\accounting\\debtor\\orm\\debtorpaymentstatus' => 'Rialto\\Accounting\\Debtor\\Orm\\DebtorPaymentStatus',
            'rialto\\accounting\\period\\web\\periodcontroller' => 'Rialto\\Accounting\\Period\\Web\\PeriodController',
            'rialto\\accounting\\web\\accountingrouter' => 'Rialto\\Accounting\\Web\\AccountingRouter',
            'rialto\\allocation\\allocation\\allocationfactory' => 'Rialto\\Allocation\\Allocation\\AllocationFactory',
            'rialto\\allocation\\allocation\\allocationtransferlistener' => 'Rialto\\Allocation\\Allocation\\AllocationTransferListener',
            'rialto\\allocation\\allocation\\emptyallocationremover' => 'Rialto\\Allocation\\Allocation\\EmptyAllocationRemover',
            'rialto\\allocation\\consumer\\stockconsumerlistener' => 'Rialto\\Allocation\\Consumer\\StockConsumerListener',
            'rialto\\allocation\\dispatch\\dispatchinstructionsubscriber' => 'Rialto\\Allocation\\Dispatch\\DispatchInstructionSubscriber',
            'rialto\\allocation\\estimatedarrivaldate\\estimatedarrivaldategenerator' => 'Rialto\\Allocation\\EstimatedArrivalDate\\EstimatedArrivalDateGenerator',
            'rialto\\allocation\\requirement\\requirementtask\\requirementtaskfactory' => 'Rialto\\Allocation\\Requirement\\RequirementTask\\RequirementTaskFactory',
            'rialto\\allocation\\validator\\purchasingdataexistsforchildvalidator' => 'Rialto\\Allocation\\Validator\\PurchasingDataExistsForChildValidator',
            'rialto\\allocation\\validator\\purchasingdataexistsvalidator' => 'Rialto\\Allocation\\Validator\\PurchasingDataExistsValidator',
            'rialto\\catalina\\catalinaclient' => 'Rialto\\Catalina\\CatalinaClient',
            'rialto\\catalina\\productiontasklistener' => 'Rialto\\Catalina\\ProductionTaskListener',
            'rialto\\ciiva\\ciivaclient' => 'Rialto\\Ciiva\\CiivaClient',
            'rialto\\cms\\cmsengine' => 'Rialto\\Cms\\CmsEngine',
            'rialto\\cms\\cmsloader' => 'Rialto\\Cms\\CmsLoader',
            'rialto\\cms\\exceptionhandler' => 'Rialto\\Cms\\ExceptionHandler',
            'rialto\\cms\\web\\cmsentrytype' => 'Rialto\\Cms\\Web\\CmsEntryType',
            'rialto\\database\\orm\\dbmanager' => 'Rialto\\Database\\Orm\\DbManager',
            'rialto\\database\\orm\\lockexceptionhandler' => 'Rialto\\Database\\Orm\\LockExceptionHandler',
            'rialto\\email\\attachment\\attachmentzipper' => 'Rialto\\Email\\Attachment\\AttachmentZipper',
            'rialto\\email\\faketransport' => 'Rialto\\Email\\FakeTransport',
            'rialto\\email\\mailerinterface' => 'Rialto\\Email\\MailerInterface',
            'rialto\\filesystem\\tempfilesystem' => 'Rialto\\Filesystem\\TempFilesystem',
            'rialto\\filetype\\pdf\\pdfgenerator' => 'Rialto\\Filetype\\Pdf\\PdfGenerator',
            'rialto\\filetype\\postscript\\fontfilesystem' => 'Rialto\\Filetype\\Postscript\\FontFilesystem',
            'rialto\\filing\\documentfilesystem' => 'Rialto\\Filing\\DocumentFilesystem',
            'rialto\\geography\\address\\web\\addressentitytype' => 'Rialto\\Geography\\Address\\Web\\AddressEntityType',
            'rialto\\geppetto\\design\\designfactory' => 'Rialto\\Geppetto\\Design\\DesignFactory',
            'rialto\\geppetto\\design\\web\\designcontroller' => 'Rialto\\Geppetto\\Design\\Web\\DesignController',
            'rialto\\geppetto\\standardcostlistener' => 'Rialto\\Geppetto\\StandardCostListener',
            'rialto\\legacy\\curlhelper' => 'Rialto\\Legacy\\CurlHelper',
            'rialto\\logging\\flashlogger' => 'Rialto\\Logging\\FlashLogger',
            'rialto\\madison\\feature\\featureinjector' => 'Rialto\\Madison\\Feature\\FeatureInjector',
            'rialto\\madison\\feature\\repository\\stockitemfeaturerepository' => 'Rialto\\Madison\\Feature\\Repository\\StockItemFeatureRepository',
            'rialto\\madison\\feature\\stockitemfeaturecalculator' => 'Rialto\\Madison\\Feature\\StockItemFeatureCalculator',
            'rialto\\madison\\feature\\web\\featuretype' => 'Rialto\\Madison\\Feature\\Web\\FeatureType',
            'rialto\\madison\\madisonclient' => 'Rialto\\Madison\\MadisonClient',
            'rialto\\madison\\version\\versionchangecache' => 'Rialto\\Madison\\Version\\VersionChangeCache',
            'rialto\\madison\\version\\versionchangenotifier' => 'Rialto\\Madison\\Version\\VersionChangeNotifier',
            'rialto\\magento2\\api\\rest\\restapifactory' => 'Rialto\\Magento2\\Api\\Rest\\RestApiFactory',
            'rialto\\magento2\\firewall\\magentoauthenticator' => 'Rialto\\Magento2\\Firewall\\MagentoAuthenticator',
            'rialto\\magento2\\firewall\\storefrontuserprovider' => 'Rialto\\Magento2\\Firewall\\StorefrontUserProvider',
            'rialto\\magento2\\order\\orderclosedlistener' => 'Rialto\\Magento2\\Order\\OrderClosedListener',
            'rialto\\magento2\\order\\ordersynchronizerinterface' => 'Rialto\\Magento2\\Order\\OrderSynchronizerInterface',
            'rialto\\magento2\\order\\paymentprocessor' => 'Rialto\\Magento2\\Order\\PaymentProcessor',
            'rialto\\magento2\\order\\shipmentlistener' => 'Rialto\\Magento2\\Order\\ShipmentListener',
            'rialto\\magento2\\order\\suspectedfraudlistener' => 'Rialto\\Magento2\\Order\\SuspectedFraudListener',
            'rialto\\magento2\\stock\\stockupdatelistener' => 'Rialto\\Magento2\\Stock\\StockUpdateListener',
            'rialto\\manufacturing\\allocation\\command\\allocatehandler' => 'Rialto\\Manufacturing\\Allocation\\Command\\AllocateHandler',
            'rialto\\manufacturing\\allocation\\orm\\stockallocationrepository' => 'Rialto\\Manufacturing\\Allocation\\Orm\\StockAllocationRepository',
            'rialto\\manufacturing\\audit\\auditadjuster' => 'Rialto\\Manufacturing\\Audit\\AuditAdjuster',
            'rialto\\manufacturing\\bom\\bag\\addbagtobomlistener' => 'Rialto\\Manufacturing\\Bom\\Bag\\AddBagToBomListener',
            'rialto\\manufacturing\\bom\\validator\\isvalidbomcsvvalidator' => 'Rialto\\Manufacturing\\Bom\\Validator\\IsValidBomCsvValidator',
            'rialto\\manufacturing\\buildfiles\\pcbbuildfilevoter' => 'Rialto\\Manufacturing\\BuildFiles\\PcbBuildFileVoter',
            'rialto\\manufacturing\\cleartobuild\\cleartobuildfactory' => 'Rialto\\Manufacturing\\ClearToBuild\\ClearToBuildFactory',
            'rialto\\manufacturing\\customization\\customizer' => 'Rialto\\Manufacturing\\Customization\\Customizer',
            'rialto\\manufacturing\\customization\\web\\customizationstrategytype' => 'Rialto\\Manufacturing\\Customization\\Web\\CustomizationStrategyType',
            'rialto\\manufacturing\\kit\\reminder\\emailscheduler' => 'Rialto\\Manufacturing\\Kit\\Reminder\\EmailScheduler',
            'rialto\\manufacturing\\purchaseorder\\command\\orderpartshandler' => 'Rialto\\Manufacturing\\PurchaseOrder\\Command\\OrderPartsHandler',
            'rialto\\manufacturing\\purchaseorder\\command\\userselectmanufacturertoorderhandler' => 'Rialto\\Manufacturing\\PurchaseOrder\\Command\\UserSelectManufacturerToOrderHandler',
            'rialto\\manufacturing\\purchaseorder\\partsordersentlistener' => 'Rialto\\Manufacturing\\PurchaseOrder\\PartsOrderSentListener',
            'rialto\\manufacturing\\requirement\\requirementfactory' => 'Rialto\\Manufacturing\\Requirement\\RequirementFactory',
            'rialto\\manufacturing\\task\\productiontaskfactory' => 'Rialto\\Manufacturing\\Task\\ProductionTaskFactory',
            'rialto\\manufacturing\\task\\productiontaskrefreshlistener' => 'Rialto\\Manufacturing\\Task\\ProductionTaskRefreshListener',
            'rialto\\manufacturing\\web\\manufacturingrouter' => 'Rialto\\Manufacturing\\Web\\ManufacturingRouter',
            'rialto\\manufacturing\\workorder\\issue\\workorderissuer' => 'Rialto\\Manufacturing\\WorkOrder\\Issue\\WorkOrderIssuer',
            'rialto\\manufacturing\\workorder\\transfereventlistener' => 'Rialto\\Manufacturing\\WorkOrder\\TransferEventListener',
            'rialto\\manufacturing\\workorder\\web\\workordercontroller' => 'Rialto\\Manufacturing\\WorkOrder\\Web\\WorkOrderController',
            'rialto\\manufacturing\\workorder\\workorderfactory' => 'Rialto\\Manufacturing\\WorkOrder\\WorkOrderFactory',
            'rialto\\manufacturing\\workorder\\workorderpdfgenerator' => 'Rialto\\Manufacturing\\WorkOrder\\WorkOrderPdfGenerator',
            'rialto\\manufacturing\\worktype\\productlabelprinter' => 'Rialto\\Manufacturing\\WorkType\\ProductLabelPrinter',
            'rialto\\panelization\\assetmanager' => 'Rialto\\Panelization\\AssetManager',
            'rialto\\panelization\\io\\panelizationstorage' => 'Rialto\\Panelization\\IO\\PanelizationStorage',
            'rialto\\panelization\\layout\\layout' => 'Rialto\\Panelization\\Layout\\Layout',
            'rialto\\panelization\\orm\\panelgateway' => 'Rialto\\Panelization\\Orm\\PanelGateway',
            'rialto\\panelization\\panelizedorderfactory' => 'Rialto\\Panelization\\PanelizedOrderFactory',
            'rialto\\panelization\\validator\\purchasingdataexistsvalidator' => 'Rialto\\Panelization\\Validator\\PurchasingDataExistsValidator',
            'rialto\\panelization\\web\\panelpdfgenerator' => 'Rialto\\Panelization\\Web\\PanelPdfGenerator',
            'rialto\\payment\\authorizenet' => 'Rialto\\Payment\\AuthorizeNet',
            'rialto\\payment\\fakegateway' => 'Rialto\\Payment\\FakeGateway',
            'rialto\\payment\\paymentgateway' => 'Rialto\\Payment\\PaymentGateway',
            'rialto\\payment\\paymentprocessor' => 'Rialto\\Payment\\PaymentProcessor',
            'rialto\\payment\\sweep\\cardtransactionsweep' => 'Rialto\\Payment\\Sweep\\CardTransactionSweep',
            'rialto\\pcbng\\command\\createmanufacturedstockitempcbngpurchasingdatahandler' => 'Rialto\\PcbNg\\Command\\CreateManufacturedStockItemPcbNgPurchasingDataHandler',
            'rialto\\pcbng\\command\\processpcbngemailshandler' => 'Rialto\\PcbNg\\Command\\ProcessPcbNgEmailsHandler',
            'rialto\\pcbng\\service\\gerbersconverter' => 'Rialto\\PcbNg\\Service\\GerbersConverter',
            'rialto\\pcbng\\service\\locationsconverter' => 'Rialto\\PcbNg\\Service\\LocationsConverter',
            'rialto\\pcbng\\service\\pcbngclient' => 'Rialto\\PcbNg\\Service\\PcbNgClient',
            'rialto\\pcbng\\service\\pcbngnotificationemailer' => 'Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer',
            'rialto\\pcbng\\service\\pcbngsubmitter' => 'Rialto\\PcbNg\\Service\\PcbNgSubmitter',
            'rialto\\pcbng\\service\\pickandplacefactory' => 'Rialto\\PcbNg\\Service\\PickAndPlaceFactory',
            'rialto\\port\\commandbus\\commandbus' => 'Rialto\\Port\\CommandBus\\CommandBus',
            'rialto\\port\\commandbus\\commandqueue' => 'Rialto\\Port\\CommandBus\\CommandQueue',
            'rialto\\port\\formatconversion\\postscripttopdfconverter' => 'Rialto\\Port\\FormatConversion\\PostScriptToPdfConverter',
            'rialto\\printing\\job\\printqueue' => 'Rialto\\Printing\\Job\\PrintQueue',
            'rialto\\printing\\printer\\printserver' => 'Rialto\\Printing\\Printer\\PrintServer',
            'rialto\\purchasing\\catalog\\cli\\purchasingdatastocklevelrefreshcommand' => 'Rialto\\Purchasing\\Catalog\\Cli\\PurchasingDataStockLevelRefreshCommand',
            'rialto\\purchasing\\catalog\\cli\\purchasingdatasynchronizercommand' => 'Rialto\\Purchasing\\Catalog\\Cli\\PurchasingDataSynchronizerCommand',
            'rialto\\purchasing\\catalog\\cli\\refreshgeppettopurchasingdataconsolecommand' => 'Rialto\\Purchasing\\Catalog\\Cli\\RefreshGeppettoPurchasingDataConsoleCommand',
            'rialto\\purchasing\\catalog\\command\\refreshpurchasingdatastocklevelhandler' => 'Rialto\\Purchasing\\Catalog\\Command\\RefreshPurchasingDataStockLevelHandler',
            'rialto\\purchasing\\catalog\\orm\\purchasingdatarepository' => 'Rialto\\Purchasing\\Catalog\\Orm\\PurchasingDataRepository',
            'rialto\\purchasing\\catalog\\purchasingdatasynchronizer' => 'Rialto\\Purchasing\\Catalog\\PurchasingDataSynchronizer',
            'rialto\\purchasing\\catalog\\remote\\octopartcatalog' => 'Rialto\\Purchasing\\Catalog\\Remote\\OctopartCatalog',
            'rialto\\purchasing\\catalog\\remote\\web\\remotecatalogcontroller' => 'Rialto\\Purchasing\\Catalog\\Remote\\Web\\RemoteCatalogController',
            'rialto\\purchasing\\catalog\\web\\purchasingdatacontroller' => 'Rialto\\Purchasing\\Catalog\\Web\\PurchasingDataController',
            'rialto\\purchasing\\emaileventsubscriber' => 'Rialto\\Purchasing\\EmailEventSubscriber',
            'rialto\\purchasing\\invoice\\command\\uploadsupplierinvoicefilehandler' => 'Rialto\\Purchasing\\Invoice\\Command\\UploadSupplierInvoiceFileHandler',
            'rialto\\purchasing\\invoice\\reader\\email\\attachmentlocator' => 'Rialto\\Purchasing\\Invoice\\Reader\\Email\\AttachmentLocator',
            'rialto\\purchasing\\invoice\\reader\\email\\attachmentparser' => 'Rialto\\Purchasing\\Invoice\\Reader\\Email\\AttachmentParser',
            'rialto\\purchasing\\invoice\\reader\\email\\suppliermailbox' => 'Rialto\\Purchasing\\Invoice\\Reader\\Email\\SupplierMailbox',
            'rialto\\purchasing\\invoice\\supplierinvoicefilesystem' => 'Rialto\\Purchasing\\Invoice\\SupplierInvoiceFilesystem',
            'rialto\\purchasing\\invoice\\supplierinvoicezipper' => 'Rialto\\Purchasing\\Invoice\\SupplierInvoiceZipper',
            'rialto\\purchasing\\invoice\\web\\supplierinvoicecontroller' => 'Rialto\\Purchasing\\Invoice\\Web\\SupplierInvoiceController',
            'rialto\\purchasing\\invoice\\web\\supplierinvoiceitemapprovaltype' => 'Rialto\\Purchasing\\Invoice\\Web\\SupplierInvoiceItemApprovalType',
            'rialto\\purchasing\\leadtime\\leadtimecalculator' => 'Rialto\\Purchasing\\LeadTime\\LeadTimeCalculator',
            'rialto\\purchasing\\manufacturer\\cli\\bulkpushmodulemanufacturersconsolecommand' => 'Rialto\\Purchasing\\Manufacturer\\Cli\\BulkPushModuleManufacturersConsoleCommand',
            'rialto\\purchasing\\manufacturer\\compliancefilesystem' => 'Rialto\\Purchasing\\Manufacturer\\ComplianceFilesystem',
            'rialto\\purchasing\\manufacturer\\logofilesystem' => 'Rialto\\Purchasing\\Manufacturer\\LogoFilesystem',
            'rialto\\purchasing\\order\\attachment\\purchaseorderattachmentgenerator' => 'Rialto\\Purchasing\\Order\\Attachment\\PurchaseOrderAttachmentGenerator',
            'rialto\\purchasing\\order\\attachment\\purchaseorderattachmentlocator' => 'Rialto\\Purchasing\\Order\\Attachment\\PurchaseOrderAttachmentLocator',
            'rialto\\purchasing\\order\\autosendreworkordersubscriber' => 'Rialto\\Purchasing\\Order\\AutoSendReworkOrderSubscriber',
            'rialto\\purchasing\\order\\command\\mergepurchaseordershandler' => 'Rialto\\Purchasing\\Order\\Command\\MergePurchaseOrdersHandler',
            'rialto\\purchasing\\order\\orderpdfgenerator' => 'Rialto\\Purchasing\\Order\\OrderPdfGenerator',
            'rialto\\purchasing\\order\\purchaseorderfactory' => 'Rialto\\Purchasing\\Order\\PurchaseOrderFactory',
            'rialto\\purchasing\\order\\purchaseordersender' => 'Rialto\\Purchasing\\Order\\PurchaseOrderSender',
            'rialto\\purchasing\\order\\purchaseordervoter' => 'Rialto\\Purchasing\\Order\\PurchaseOrderVoter',
            'rialto\\purchasing\\order\\stockitemvoter' => 'Rialto\\Purchasing\\Order\\StockItemVoter',
            'rialto\\purchasing\\order\\web\\createpurchaseordertype' => 'Rialto\\Purchasing\\Order\\Web\\CreatePurchaseOrderType',
            'rialto\\purchasing\\order\\web\\editpurchaseordertype' => 'Rialto\\Purchasing\\Order\\Web\\EditPurchaseOrderType',
            'rialto\\purchasing\\order\\web\\purchaseordercontroller' => 'Rialto\\Purchasing\\Order\\Web\\PurchaseOrderController',
            'rialto\\purchasing\\producer\\commitmentdateestimator\\stockproducercommitmentdateestimator' => 'Rialto\\Purchasing\\Producer\\CommitmentDateEstimator\\StockProducerCommitmentDateEstimator',
            'rialto\\purchasing\\producer\\dependencyupdater' => 'Rialto\\Purchasing\\Producer\\DependencyUpdater',
            'rialto\\purchasing\\producer\\stockproducerfactory' => 'Rialto\\Purchasing\\Producer\\StockProducerFactory',
            'rialto\\purchasing\\producer\\stockproducervoter' => 'Rialto\\Purchasing\\Producer\\StockProducerVoter',
            'rialto\\purchasing\\producer\\web\\stockproducertype' => 'Rialto\\Purchasing\\Producer\\Web\\StockProducerType',
            'rialto\\purchasing\\purchasingerrorhandler' => 'Rialto\\Purchasing\\PurchasingErrorHandler',
            'rialto\\purchasing\\receiving\\auth\\canreceiveintovalidator' => 'Rialto\\Purchasing\\Receiving\\Auth\\CanReceiveIntoValidator',
            'rialto\\purchasing\\receiving\\auth\\receiveintovoter' => 'Rialto\\Purchasing\\Receiving\\Auth\\ReceiveIntoVoter',
            'rialto\\purchasing\\receiving\\goodsreceivedlogger' => 'Rialto\\Purchasing\\Receiving\\GoodsReceivedLogger',
            'rialto\\purchasing\\receiving\\notify\\xmppeventsubscriber' => 'Rialto\\Purchasing\\Receiving\\Notify\\XmppEventSubscriber',
            'rialto\\purchasing\\receiving\\receiver' => 'Rialto\\Purchasing\\Receiving\\Receiver',
            'rialto\\purchasing\\receiving\\web\\goodsreceivedtype' => 'Rialto\\Purchasing\\Receiving\\Web\\GoodsReceivedType',
            'rialto\\purchasing\\supplier\\attribute\\web\\supplierattributetype' => 'Rialto\\Purchasing\\Supplier\\Attribute\\Web\\SupplierAttributeType',
            'rialto\\purchasing\\supplier\\supplierpaymentstatus' => 'Rialto\\Purchasing\\Supplier\\SupplierPaymentStatus',
            'rialto\\purchasing\\supplier\\web\\actionscontroller' => 'Rialto\\Purchasing\\Supplier\\Web\\ActionsController',
            'rialto\\purchasing\\web\\purchasingrouter' => 'Rialto\\Purchasing\\Web\\PurchasingRouter',
            'rialto\\sales\\discount\\discountcalculator' => 'Rialto\\Sales\\Discount\\DiscountCalculator',
            'rialto\\sales\\documenteventlistener' => 'Rialto\\Sales\\DocumentEventListener',
            'rialto\\sales\\emaileventlistener' => 'Rialto\\Sales\\EmailEventListener',
            'rialto\\sales\\invoice\\label\\ecialabelmanager' => 'Rialto\\Sales\\Invoice\\Label\\EciaLabelManager',
            'rialto\\sales\\invoice\\salesinvoiceprocessor' => 'Rialto\\Sales\\Invoice\\SalesInvoiceProcessor',
            'rialto\\sales\\order\\allocation\\allocationeventlistener' => 'Rialto\\Sales\\Order\\Allocation\\AllocationEventListener',
            'rialto\\sales\\order\\allocation\\command\\createstockitemorderhandler' => 'Rialto\\Sales\\Order\\Allocation\\Command\\CreateStockItemOrderHandler',
            'rialto\\sales\\order\\customerpartnopopulator' => 'Rialto\\Sales\\Order\\CustomerPartNoPopulator',
            'rialto\\sales\\order\\dates\\targetshipdatecalculator' => 'Rialto\\Sales\\Order\\Dates\\TargetShipDateCalculator',
            'rialto\\sales\\order\\dates\\targetshipdatelistener' => 'Rialto\\Sales\\Order\\Dates\\TargetShipDateListener',
            'rialto\\sales\\order\\dates\\web\\orderdatecontroller' => 'Rialto\\Sales\\Order\\Dates\\Web\\OrderDateController',
            'rialto\\sales\\order\\email\\orderemaillistener' => 'Rialto\\Sales\\Order\\Email\\OrderEmailListener',
            'rialto\\sales\\order\\email\\ordertoemailfilter' => 'Rialto\\Sales\\Order\\Email\\OrderToEmailFilter',
            'rialto\\sales\\order\\import\\orderimporter' => 'Rialto\\Sales\\Order\\Import\\OrderImporter',
            'rialto\\sales\\order\\orderupdatelistener' => 'Rialto\\Sales\\Order\\OrderUpdateListener',
            'rialto\\sales\\order\\salesorderpaymentprocessor' => 'Rialto\\Sales\\Order\\SalesOrderPaymentProcessor',
            'rialto\\sales\\order\\softwareinvoicer' => 'Rialto\\Sales\\Order\\SoftwareInvoicer',
            'rialto\\sales\\returns\\disposition\\salesreturndisposition' => 'Rialto\\Sales\\Returns\\Disposition\\SalesReturnDisposition',
            'rialto\\sales\\returns\\receipt\\salesreturnreceiver' => 'Rialto\\Sales\\Returns\\Receipt\\SalesReturnReceiver',
            'rialto\\sales\\saleslogger' => 'Rialto\\Sales\\SalesLogger',
            'rialto\\sales\\salespdfgenerator' => 'Rialto\\Sales\\SalesPdfGenerator',
            'rialto\\sales\\salesprintmanager' => 'Rialto\\Sales\\SalesPrintManager',
            'rialto\\sales\\shipping\\approvetoshipeventlistener' => 'Rialto\\Sales\\Shipping\\ApproveToShipEventListener',
            'rialto\\sales\\shipping\\salesordershippingapproval' => 'Rialto\\Sales\\Shipping\\SalesOrderShippingApproval',
            'rialto\\sales\\web\\salesrouter' => 'Rialto\\Sales\\Web\\SalesRouter',
            'rialto\\security\\firewall\\byusernameprovider' => 'Rialto\\Security\\Firewall\\ByUsernameProvider',
            'rialto\\security\\firewall\\byuuidprovider' => 'Rialto\\Security\\Firewall\\ByUuidProvider',
            'rialto\\security\\firewall\\usernamenotfoundexceptionhandler' => 'Rialto\\Security\\Firewall\\UsernameNotFoundExceptionHandler',
            'rialto\\security\\nda\\ndaformlistener' => 'Rialto\\Security\\Nda\\NdaFormListener',
            'rialto\\security\\user\\lastloginupdater' => 'Rialto\\Security\\User\\LastLoginUpdater',
            'rialto\\security\\user\\usermanager' => 'Rialto\\Security\\User\\UserManager',
            'rialto\\security\\user\\uservoter' => 'Rialto\\Security\\User\\UserVoter',
            'rialto\\security\\user\\web\\usertype' => 'Rialto\\Security\\User\\Web\\UserType',
            'rialto\\shipping\\export\\allowedcountryvalidator' => 'Rialto\\Shipping\\Export\\AllowedCountryValidator',
            'rialto\\shipping\\export\\deniedpartyscreener' => 'Rialto\\Shipping\\Export\\DeniedPartyScreener',
            'rialto\\shipping\\method\\shippingtimeestimator\\shippingtimeestimatorinterface' => 'Rialto\\Shipping\\Method\\ShippingTimeEstimator\\ShippingTimeEstimatorInterface',
            'rialto\\shipping\\shipment\\shipmentfactory' => 'Rialto\\Shipping\\Shipment\\ShipmentFactory',
            'rialto\\shipping\\shipment\\web\\shipmentoptionstype' => 'Rialto\\Shipping\\Shipment\\Web\\ShipmentOptionsType',
            'rialto\\shopify\\order\\fulfillmentlistener' => 'Rialto\\Shopify\\Order\\FulfillmentListener',
            'rialto\\shopify\\order\\orderclosedlistener' => 'Rialto\\Shopify\\Order\\OrderClosedListener',
            'rialto\\shopify\\order\\paymentprocessor' => 'Rialto\\Shopify\\Order\\PaymentProcessor',
            'rialto\\shopify\\webhook\\shopifyuserprovider' => 'Rialto\\Shopify\\Webhook\\ShopifyUserProvider',
            'rialto\\shopify\\webhook\\webhookauthenticator' => 'Rialto\\Shopify\\Webhook\\WebhookAuthenticator',
            'rialto\\stock\\bin\\label\\binlabellistener' => 'Rialto\\Stock\\Bin\\Label\\BinLabelListener',
            'rialto\\stock\\bin\\label\\binlabelprintqueue' => 'Rialto\\Stock\\Bin\\Label\\BinLabelPrintQueue',
            'rialto\\stock\\bin\\label\\web\\labelcontroller' => 'Rialto\\Stock\\Bin\\Label\\Web\\LabelController',
            'rialto\\stock\\bin\\stockbinsplitter' => 'Rialto\\Stock\\Bin\\StockBinSplitter',
            'rialto\\stock\\bin\\stockbinupdatelistener' => 'Rialto\\Stock\\Bin\\StockBinUpdateListener',
            'rialto\\stock\\bin\\stockbinvoter' => 'Rialto\\Stock\\Bin\\StockBinVoter',
            'rialto\\stock\\bin\\web\\binupdatealloctype' => 'Rialto\\Stock\\Bin\\Web\\BinUpdateAllocType',
            'rialto\\stock\\bin\\web\\stockadjustmenttype' => 'Rialto\\Stock\\Bin\\Web\\StockAdjustmentType',
            'rialto\\stock\\bin\\web\\stockbincontroller' => 'Rialto\\Stock\\Bin\\Web\\StockBinController',
            'rialto\\stock\\category\\categorychange' => 'Rialto\\Stock\\Category\\CategoryChange',
            'rialto\\stock\\cost\\standardcostupdater' => 'Rialto\\Stock\\Cost\\StandardCostUpdater',
            'rialto\\stock\\count\\stockcountvoter' => 'Rialto\\Stock\\Count\\StockCountVoter',
            'rialto\\stock\\count\\web\\csvstockcountflow' => 'Rialto\\Stock\\Count\\Web\\CsvStockCountFlow',
            'rialto\\stock\\emaileventlistener' => 'Rialto\\Stock\\EmailEventListener',
            'rialto\\stock\\item\\batchstockupdater' => 'Rialto\\Stock\\Item\\BatchStockUpdater',
            'rialto\\stock\\item\\cli\\stocklevelrefreshcommand' => 'Rialto\\Stock\\Item\\Cli\\StockLevelRefreshCommand',
            'rialto\\stock\\item\\command\\refreshstocklevelhandler' => 'Rialto\\Stock\\Item\\Command\\RefreshStockLevelHandler',
            'rialto\\stock\\item\\newskuvalidator' => 'Rialto\\Stock\\Item\\NewSkuValidator',
            'rialto\\stock\\item\\stockitemdeleteservice' => 'Rialto\\Stock\\Item\\StockItemDeleteService',
            'rialto\\stock\\item\\stockitemfactory' => 'Rialto\\Stock\\Item\\StockItemFactory',
            'rialto\\stock\\item\\version\\web\\itemversionselectortype' => 'Rialto\\Stock\\Item\\Version\\Web\\ItemVersionSelectorType',
            'rialto\\stock\\item\\web\\actionscontroller' => 'Rialto\\Stock\\Item\\Web\\ActionsController',
            'rialto\\stock\\item\\web\\edittype' => 'Rialto\\Stock\\Item\\Web\\EditType',
            'rialto\\stock\\item\\web\\stockitemattributetype' => 'Rialto\\Stock\\Item\\Web\\StockItemAttributeType',
            'rialto\\stock\\item\\web\\stockitemtemplatetype' => 'Rialto\\Stock\\Item\\Web\\StockItemTemplateType',
            'rialto\\stock\\level\\stocklevelservice' => 'Rialto\\Stock\\Level\\StockLevelService',
            'rialto\\stock\\level\\stocklevelsynchronizer' => 'Rialto\\Stock\\Level\\StockLevelSynchronizer',
            'rialto\\stock\\publication\\publicationfilesystem' => 'Rialto\\Stock\\Publication\\PublicationFilesystem',
            'rialto\\stock\\publication\\publicationprintmanager' => 'Rialto\\Stock\\Publication\\PublicationPrintManager',
            'rialto\\stock\\returns\\problem\\returneditemresolver' => 'Rialto\\Stock\\Returns\\Problem\\ReturnedItemResolver',
            'rialto\\stock\\returns\\returneditemservice' => 'Rialto\\Stock\\Returns\\ReturnedItemService',
            'rialto\\stock\\returns\\web\\returneditemsflow' => 'Rialto\\Stock\\Returns\\Web\\ReturnedItemsFlow',
            'rialto\\stock\\shelf\\position\\assignmentlistener' => 'Rialto\\Stock\\Shelf\\Position\\AssignmentListener',
            'rialto\\stock\\shelf\\position\\positionassigner' => 'Rialto\\Stock\\Shelf\\Position\\PositionAssigner',
            'rialto\\stock\\transfer\\bineventlistener' => 'Rialto\\Stock\\Transfer\\BinEventListener',
            'rialto\\stock\\transfer\\transferreceiver' => 'Rialto\\Stock\\Transfer\\TransferReceiver',
            'rialto\\stock\\transfer\\transferservice' => 'Rialto\\Stock\\Transfer\\TransferService',
            'rialto\\stock\\web\\stockrouter' => 'Rialto\\Stock\\Web\\StockRouter',
            'rialto\\summary\\menu\\summaryvoter' => 'Rialto\\Summary\\Menu\\SummaryVoter',
            'rialto\\supplier\\allocation\\web\\binallocationcontroller' => 'Rialto\\Supplier\\Allocation\\Web\\BinAllocationController',
            'rialto\\supplier\\logger' => 'Rialto\\Supplier\\Logger',
            'rialto\\supplier\\order\\email\\emailsubscriber' => 'Rialto\\Supplier\\Order\\Email\\EmailSubscriber',
            'rialto\\supplier\\order\\web\\trackingfacades\\supplierinvoicetrackingfacadesfactory' => 'Rialto\\Supplier\\Order\\Web\\TrackingFacades\\SupplierInvoiceTrackingFacadesFactory',
            'rialto\\supplier\\order\\web\\workordercontroller' => 'Rialto\\Supplier\\Order\\Web\\WorkOrderController',
            'rialto\\supplier\\suppliervoter' => 'Rialto\\Supplier\\SupplierVoter',
            'rialto\\task\\taskvoter' => 'Rialto\\Task\\TaskVoter',
            'rialto\\tax\\taxlookup' => 'Rialto\\Tax\\TaxLookup',
            'rialto\\ups\\invoice\\invoiceloader' => 'Rialto\\Ups\\Invoice\\InvoiceLoader',
            'rialto\\ups\\shipping\\label\\shippinglabellistener' => 'Rialto\\Ups\\Shipping\\Label\\ShippingLabelListener',
            'rialto\\ups\\shipping\\webservice\\upsapiservice' => 'Rialto\\Ups\\Shipping\\Webservice\\UpsApiService',
            'rialto\\ups\\trackingrecord\\cli\\polltrackingnumberscommand' => 'Rialto\\Ups\\TrackingRecord\\Cli\\PollTrackingNumbersCommand',
            'rialto\\ups\\trackingrecord\\cli\\updatepotrackingrecordscommand' => 'Rialto\\Ups\\TrackingRecord\\Cli\\UpdatePOTrackingRecordsCommand',
            'rialto\\ups\\trackingrecord\\cli\\updatesalestrackingrecordscommand' => 'Rialto\\Ups\\TrackingRecord\\Cli\\UpdateSalesTrackingRecordsCommand',
            'rialto\\ups\\trackingrecord\\command\\updatetrackingrecordhandler' => 'Rialto\\Ups\\TrackingRecord\\Command\\UpdateTrackingRecordHandler',
            'rialto\\web\\form\\jsentitytype' => 'Rialto\\Web\\Form\\JsEntityType',
            'rialto\\web\\form\\numbertypeextension' => 'Rialto\\Web\\Form\\NumberTypeExtension',
            'rialto\\web\\form\\textentitytype' => 'Rialto\\Web\\Form\\TextEntityType',
            'rialto\\web\\form\\validator' => 'Rialto\\Web\\Form\\Validator',
            'rialto\\wordpress\\changenoticelistener' => 'Rialto\\Wordpress\\ChangeNoticeListener',
            'symfony\\bundle\\frameworkbundle\\controller\\redirectcontroller' => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\RedirectController',
            'symfony\\bundle\\frameworkbundle\\controller\\templatecontroller' => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\TemplateController',
            'symfony\\component\\eventdispatcher\\eventdispatcherinterface' => 'Symfony\\Component\\EventDispatcher\\EventDispatcherInterface',
            'symfony\\component\\form\\formfactoryinterface' => 'Symfony\\Component\\Form\\FormFactoryInterface',
            'symfony\\component\\httpfoundation\\requeststack' => 'Symfony\\Component\\HttpFoundation\\RequestStack',
            'symfony\\component\\httpfoundation\\session\\session' => 'Symfony\\Component\\HttpFoundation\\Session\\Session',
            'symfony\\component\\httpfoundation\\session\\sessioninterface' => 'Symfony\\Component\\HttpFoundation\\Session\\SessionInterface',
            'symfony\\component\\routing\\routerinterface' => 'Symfony\\Component\\Routing\\RouterInterface',
            'symfony\\component\\security\\core\\authorization\\authorizationcheckerinterface' => 'Symfony\\Component\\Security\\Core\\Authorization\\AuthorizationCheckerInterface',
            'symfony\\component\\validator\\validator\\validatorinterface' => 'Symfony\\Component\\Validator\\Validator\\ValidatorInterface',
            'symfony\\webpackencorebundle\\asset\\entrypointlookupinterface' => 'Symfony\\WebpackEncoreBundle\\Asset\\EntrypointLookupInterface',
            'twig_environment' => 'Twig_Environment',
        ];
        $this->syntheticIds = [
            'kernel' => true,
        ];
        $this->methodMap = [
            'Doctrine\\Common\\Persistence\\ObjectManager' => 'getObjectManagerService',
            'Gumstix\\GeographyBundle\\Twig\\GeographyExtension' => 'getGeographyExtensionService',
            'Gumstix\\SSOBundle\\Twig\\SSOExtension' => 'getSSOExtensionService',
            'Gumstix\\Storage\\FileStorage' => 'getFileStorageService',
            'Rialto\\Accounting\\Web\\AccountingRouter' => 'getAccountingRouterService',
            'Rialto\\Allocation\\EstimatedArrivalDate\\EstimatedArrivalDateGenerator' => 'getEstimatedArrivalDateGeneratorService',
            'Rialto\\Cms\\CmsEngine' => 'getCmsEngineService',
            'Rialto\\Cms\\CmsLoader' => 'getCmsLoaderService',
            'Rialto\\Filetype\\Postscript\\FontFilesystem' => 'getFontFilesystemService',
            'Rialto\\Madison\\Version\\VersionChangeCache' => 'getVersionChangeCacheService',
            'Rialto\\Manufacturing\\Task\\ProductionTaskRefreshListener' => 'getProductionTaskRefreshListenerService',
            'Rialto\\Manufacturing\\Web\\ManufacturingRouter' => 'getManufacturingRouterService',
            'Rialto\\Printing\\Printer\\PrintServer' => 'getPrintServerService',
            'Rialto\\Purchasing\\Catalog\\Orm\\PurchasingDataRepository' => 'getPurchasingDataRepositoryService',
            'Rialto\\Purchasing\\Invoice\\SupplierInvoiceFilesystem' => 'getSupplierInvoiceFilesystemService',
            'Rialto\\Purchasing\\Producer\\CommitmentDateEstimator\\StockProducerCommitmentDateEstimator' => 'getStockProducerCommitmentDateEstimatorService',
            'Rialto\\Purchasing\\Web\\PurchasingRouter' => 'getPurchasingRouterService',
            'Rialto\\Sales\\Web\\SalesRouter' => 'getSalesRouterService',
            'Rialto\\Security\\Nda\\NdaFormListener' => 'getNdaFormListenerService',
            'Rialto\\Security\\User\\UserManager' => 'getUserManagerService',
            'Rialto\\Shipping\\Method\\ShippingTimeEstimator\\ShippingTimeEstimatorInterface' => 'getShippingTimeEstimatorInterfaceService',
            'Rialto\\Stock\\Web\\StockRouter' => 'getStockRouterService',
            'Symfony\\Component\\Routing\\RouterInterface' => 'getRouterInterfaceService',
            'annotation_reader' => 'getAnnotationReaderService',
            'annotations.reader' => 'getAnnotations_ReaderService',
            'assets._version__default' => 'getAssets_VersionDefaultService',
            'assets.context' => 'getAssets_ContextService',
            'assets.packages' => 'getAssets_PackagesService',
            'config_cache_factory' => 'getConfigCacheFactoryService',
            'controller_name_converter' => 'getControllerNameConverterService',
            'craue_formflow_util' => 'getCraueFormflowUtilService',
            'debug.argument_resolver' => 'getDebug_ArgumentResolverService',
            'debug.controller_resolver' => 'getDebug_ControllerResolverService',
            'debug.debug_handlers_listener' => 'getDebug_DebugHandlersListenerService',
            'debug.event_dispatcher' => 'getDebug_EventDispatcherService',
            'debug.file_link_formatter' => 'getDebug_FileLinkFormatterService',
            'debug.security.access.decision_manager' => 'getDebug_Security_Access_DecisionManagerService',
            'debug.stopwatch' => 'getDebug_StopwatchService',
            'doctrine' => 'getDoctrineService',
            'doctrine.dbal.connection_factory' => 'getDoctrine_Dbal_ConnectionFactoryService',
            'doctrine.dbal.default_connection' => 'getDoctrine_Dbal_DefaultConnectionService',
            'doctrine.orm.default_entity_listener_resolver' => 'getDoctrine_Orm_DefaultEntityListenerResolverService',
            'doctrine.orm.default_entity_manager' => 'getDoctrine_Orm_DefaultEntityManagerService',
            'doctrine.orm.default_listeners.attach_entity_listeners' => 'getDoctrine_Orm_DefaultListeners_AttachEntityListenersService',
            'doctrine.orm.default_manager_configurator' => 'getDoctrine_Orm_DefaultManagerConfiguratorService',
            'doctrine.orm.validator_initializer' => 'getDoctrine_Orm_ValidatorInitializerService',
            'doctrine_cache.providers.doctrine.orm.default_metadata_cache' => 'getDoctrineCache_Providers_Doctrine_Orm_DefaultMetadataCacheService',
            'doctrine_cache.providers.doctrine.orm.default_query_cache' => 'getDoctrineCache_Providers_Doctrine_Orm_DefaultQueryCacheService',
            'doctrine_cache.providers.doctrine.orm.default_result_cache' => 'getDoctrineCache_Providers_Doctrine_Orm_DefaultResultCacheService',
            'easyadmin.cache.manager' => 'getEasyadmin_Cache_ManagerService',
            'easyadmin.config.manager' => 'getEasyadmin_Config_ManagerService',
            'easyadmin.listener.controller' => 'getEasyadmin_Listener_ControllerService',
            'easyadmin.router' => 'getEasyadmin_RouterService',
            'file_locator' => 'getFileLocatorService',
            'form.registry' => 'getForm_RegistryService',
            'form.resolved_type_factory' => 'getForm_ResolvedTypeFactoryService',
            'fos_rest.body_listener' => 'getFosRest_BodyListenerService',
            'fos_rest.decoder_provider' => 'getFosRest_DecoderProviderService',
            'fos_rest.format_listener' => 'getFosRest_FormatListenerService',
            'fos_rest.format_negotiator' => 'getFosRest_FormatNegotiatorService',
            'gumstix_form.twig_extension' => 'getGumstixForm_TwigExtensionService',
            'gumstix_sso.router' => 'getGumstixSso_RouterService',
            'http_kernel' => 'getHttpKernelService',
            'jms_job_queue.twig.extension' => 'getJmsJobQueue_Twig_ExtensionService',
            'locale_listener' => 'getLocaleListenerService',
            'monolog.handler.console' => 'getMonolog_Handler_ConsoleService',
            'monolog.handler.doctrine' => 'getMonolog_Handler_DoctrineService',
            'monolog.handler.php' => 'getMonolog_Handler_PhpService',
            'monolog.handler.sentry' => 'getMonolog_Handler_SentryService',
            'monolog.logger.doctrine' => 'getMonolog_Logger_DoctrineService',
            'monolog.logger.event' => 'getMonolog_Logger_EventService',
            'monolog.logger.php' => 'getMonolog_Logger_PhpService',
            'monolog.logger.request' => 'getMonolog_Logger_RequestService',
            'monolog.logger.translation' => 'getMonolog_Logger_TranslationService',
            'monolog.processor.psr_log_message' => 'getMonolog_Processor_PsrLogMessageService',
            'nelmio_cors.cors_listener' => 'getNelmioCors_CorsListenerService',
            'nelmio_cors.options_provider.config' => 'getNelmioCors_OptionsProvider_ConfigService',
            'nelmio_security.clickjacking_listener' => 'getNelmioSecurity_ClickjackingListenerService',
            'nelmio_security.external_redirect.target_validator' => 'getNelmioSecurity_ExternalRedirect_TargetValidatorService',
            'nelmio_security.external_redirect_listener' => 'getNelmioSecurity_ExternalRedirectListenerService',
            'property_accessor' => 'getPropertyAccessorService',
            'request_stack' => 'getRequestStackService',
            'resolve_controller_name_subscriber' => 'getResolveControllerNameSubscriberService',
            'response_listener' => 'getResponseListenerService',
            'router.request_context' => 'getRouter_RequestContextService',
            'router_listener' => 'getRouterListenerService',
            'security.authentication.manager' => 'getSecurity_Authentication_ManagerService',
            'security.authorization_checker' => 'getSecurity_AuthorizationCheckerService',
            'security.firewall' => 'getSecurity_FirewallService',
            'security.logout_url_generator' => 'getSecurity_LogoutUrlGeneratorService',
            'security.rememberme.response_listener' => 'getSecurity_Rememberme_ResponseListenerService',
            'security.token_storage' => 'getSecurity_TokenStorageService',
            'sensio_framework_extra.controller.listener' => 'getSensioFrameworkExtra_Controller_ListenerService',
            'sensio_framework_extra.converter.datetime' => 'getSensioFrameworkExtra_Converter_DatetimeService',
            'sensio_framework_extra.converter.doctrine.orm' => 'getSensioFrameworkExtra_Converter_Doctrine_OrmService',
            'sensio_framework_extra.converter.listener' => 'getSensioFrameworkExtra_Converter_ListenerService',
            'sensio_framework_extra.converter.manager' => 'getSensioFrameworkExtra_Converter_ManagerService',
            'sensio_framework_extra.view.listener' => 'getSensioFrameworkExtra_View_ListenerService',
            'session.save_listener' => 'getSession_SaveListenerService',
            'session_listener' => 'getSessionListenerService',
            'streamed_response_listener' => 'getStreamedResponseListenerService',
            'templating.locator' => 'getTemplating_LocatorService',
            'templating.name_parser' => 'getTemplating_NameParserService',
            'translator' => 'getTranslatorService',
            'translator.default' => 'getTranslator_DefaultService',
            'translator_listener' => 'getTranslatorListenerService',
            'twig' => 'getTwigService',
            'twig.extension.craue_formflow' => 'getTwig_Extension_CraueFormflowService',
            'twig.extension.routing' => 'getTwig_Extension_RoutingService',
            'twig.loader' => 'getTwig_LoaderService',
            'twig.loader.filesystem' => 'getTwig_Loader_FilesystemService',
            'twig.profile' => 'getTwig_ProfileService',
            'validate_request_listener' => 'getValidateRequestListenerService',
            'validator' => 'getValidatorService',
            'validator.builder' => 'getValidator_BuilderService',
            'web_profiler.csp.handler' => 'getWebProfiler_Csp_HandlerService',
            'web_profiler.debug_toolbar' => 'getWebProfiler_DebugToolbarService',
        ];
        $this->fileMap = [
            'FOS\\RestBundle\\View\\ViewHandlerInterface' => 'getViewHandlerInterfaceService.php',
            'Fabiang\\Xmpp\\Client' => 'getClientService.php',
            'Gumstix\\RestBundle\\Handler\\AccessDeniedHandler' => 'getAccessDeniedHandlerService.php',
            'Gumstix\\RestBundle\\Serializer\\FormErrorNormalizer' => 'getFormErrorNormalizerService.php',
            'Gumstix\\SSOBundle\\Security\\CookieAuthenticator' => 'getCookieAuthenticatorService.php',
            'Gumstix\\SSOBundle\\Security\\HeaderAuthenticator' => 'getHeaderAuthenticatorService.php',
            'Gumstix\\SSOBundle\\Security\\LoginAuthenticator' => 'getLoginAuthenticatorService.php',
            'Gumstix\\SSOBundle\\Service\\HttpClientFactory' => 'getHttpClientFactoryService.php',
            'Gumstix\\SSOBundle\\Service\\LogoutService' => 'getLogoutServiceService.php',
            'Gumstix\\SSOBundle\\Service\\SingleSignOnFactory' => 'getSingleSignOnFactoryService.php',
            'Gumstix\\SSO\\Service\\CredentialStorage' => 'getCredentialStorageService.php',
            'Gumstix\\SSO\\Service\\Router' => 'getRouterService.php',
            'Gumstix\\SSO\\Service\\SingleSignOn' => 'getSingleSignOnService.php',
            'JMS\\Serializer\\SerializerInterface' => 'getSerializerInterfaceService.php',
            'MongoDB\\Database' => 'getDatabaseService.php',
            'Rialto\\Accounting\\Bank\\Account\\AvailableChequeNumberValidator' => 'getAvailableChequeNumberValidatorService.php',
            'Rialto\\Accounting\\Bank\\Account\\Repository\\BankAccountRepository' => 'getBankAccountRepositoryService.php',
            'Rialto\\Accounting\\Bank\\Transfer\\BankTransferController' => 'getBankTransferControllerService.php',
            'Rialto\\Accounting\\Debtor\\DebtorTransactionFactory' => 'getDebtorTransactionFactoryService.php',
            'Rialto\\Accounting\\Debtor\\Orm\\DebtorPaymentStatus' => 'getDebtorPaymentStatusService.php',
            'Rialto\\Accounting\\Period\\Web\\PeriodController' => 'getPeriodControllerService.php',
            'Rialto\\Allocation\\Allocation\\AllocationFactory' => 'getAllocationFactoryService.php',
            'Rialto\\Allocation\\Allocation\\AllocationTransferListener' => 'getAllocationTransferListenerService.php',
            'Rialto\\Allocation\\Allocation\\EmptyAllocationRemover' => 'getEmptyAllocationRemoverService.php',
            'Rialto\\Allocation\\Consumer\\StockConsumerListener' => 'getStockConsumerListenerService.php',
            'Rialto\\Allocation\\Dispatch\\DispatchInstructionSubscriber' => 'getDispatchInstructionSubscriberService.php',
            'Rialto\\Allocation\\Requirement\\RequirementTask\\RequirementTaskFactory' => 'getRequirementTaskFactoryService.php',
            'Rialto\\Allocation\\Validator\\PurchasingDataExistsForChildValidator' => 'getPurchasingDataExistsForChildValidatorService.php',
            'Rialto\\Allocation\\Validator\\PurchasingDataExistsValidator' => 'getPurchasingDataExistsValidatorService.php',
            'Rialto\\Catalina\\CatalinaClient' => 'getCatalinaClientService.php',
            'Rialto\\Catalina\\ProductionTaskListener' => 'getProductionTaskListenerService.php',
            'Rialto\\Ciiva\\CiivaClient' => 'getCiivaClientService.php',
            'Rialto\\Cms\\ExceptionHandler' => 'getExceptionHandlerService.php',
            'Rialto\\Cms\\Web\\CmsEntryType' => 'getCmsEntryTypeService.php',
            'Rialto\\Database\\Orm\\LockExceptionHandler' => 'getLockExceptionHandlerService.php',
            'Rialto\\Email\\Attachment\\AttachmentZipper' => 'getAttachmentZipperService.php',
            'Rialto\\Email\\FakeTransport' => 'getFakeTransportService.php',
            'Rialto\\Email\\MailerInterface' => 'getMailerInterfaceService.php',
            'Rialto\\Filesystem\\TempFilesystem' => 'getTempFilesystemService.php',
            'Rialto\\Filetype\\Pdf\\PdfGenerator' => 'getPdfGeneratorService.php',
            'Rialto\\Filing\\DocumentFilesystem' => 'getDocumentFilesystemService.php',
            'Rialto\\Geography\\Address\\Web\\AddressEntityType' => 'getAddressEntityTypeService.php',
            'Rialto\\Geppetto\\Design\\DesignFactory' => 'getDesignFactoryService.php',
            'Rialto\\Geppetto\\Design\\Web\\DesignController' => 'getDesignControllerService.php',
            'Rialto\\Geppetto\\StandardCostListener' => 'getStandardCostListenerService.php',
            'Rialto\\Legacy\\CurlHelper' => 'getCurlHelperService.php',
            'Rialto\\Logging\\FlashLogger' => 'getFlashLoggerService.php',
            'Rialto\\Madison\\Feature\\FeatureInjector' => 'getFeatureInjectorService.php',
            'Rialto\\Madison\\Feature\\Repository\\StockItemFeatureRepository' => 'getStockItemFeatureRepositoryService.php',
            'Rialto\\Madison\\Feature\\StockItemFeatureCalculator' => 'getStockItemFeatureCalculatorService.php',
            'Rialto\\Madison\\Feature\\Web\\FeatureType' => 'getFeatureTypeService.php',
            'Rialto\\Madison\\MadisonClient' => 'getMadisonClientService.php',
            'Rialto\\Madison\\Version\\VersionChangeNotifier' => 'getVersionChangeNotifierService.php',
            'Rialto\\Magento2\\Api\\Rest\\RestApiFactory' => 'getRestApiFactoryService.php',
            'Rialto\\Magento2\\Firewall\\MagentoAuthenticator' => 'getMagentoAuthenticatorService.php',
            'Rialto\\Magento2\\Firewall\\StorefrontUserProvider' => 'getStorefrontUserProviderService.php',
            'Rialto\\Magento2\\Order\\OrderClosedListener' => 'getOrderClosedListenerService.php',
            'Rialto\\Magento2\\Order\\OrderSynchronizerInterface' => 'getOrderSynchronizerInterfaceService.php',
            'Rialto\\Magento2\\Order\\PaymentProcessor' => 'getPaymentProcessorService.php',
            'Rialto\\Magento2\\Order\\ShipmentListener' => 'getShipmentListenerService.php',
            'Rialto\\Magento2\\Order\\SuspectedFraudListener' => 'getSuspectedFraudListenerService.php',
            'Rialto\\Magento2\\Stock\\StockUpdateListener' => 'getStockUpdateListenerService.php',
            'Rialto\\Manufacturing\\Allocation\\Command\\AllocateHandler' => 'getAllocateHandlerService.php',
            'Rialto\\Manufacturing\\Allocation\\Orm\\StockAllocationRepository' => 'getStockAllocationRepositoryService.php',
            'Rialto\\Manufacturing\\Audit\\AuditAdjuster' => 'getAuditAdjusterService.php',
            'Rialto\\Manufacturing\\Bom\\Bag\\AddBagToBomListener' => 'getAddBagToBomListenerService.php',
            'Rialto\\Manufacturing\\Bom\\Validator\\IsValidBomCsvValidator' => 'getIsValidBomCsvValidatorService.php',
            'Rialto\\Manufacturing\\BuildFiles\\PcbBuildFileVoter' => 'getPcbBuildFileVoterService.php',
            'Rialto\\Manufacturing\\ClearToBuild\\ClearToBuildFactory' => 'getClearToBuildFactoryService.php',
            'Rialto\\Manufacturing\\Customization\\Customizer' => 'getCustomizerService.php',
            'Rialto\\Manufacturing\\Customization\\Web\\CustomizationStrategyType' => 'getCustomizationStrategyTypeService.php',
            'Rialto\\Manufacturing\\Kit\\Reminder\\EmailScheduler' => 'getEmailSchedulerService.php',
            'Rialto\\Manufacturing\\PurchaseOrder\\Command\\OrderPartsHandler' => 'getOrderPartsHandlerService.php',
            'Rialto\\Manufacturing\\PurchaseOrder\\Command\\UserSelectManufacturerToOrderHandler' => 'getUserSelectManufacturerToOrderHandlerService.php',
            'Rialto\\Manufacturing\\PurchaseOrder\\PartsOrderSentListener' => 'getPartsOrderSentListenerService.php',
            'Rialto\\Manufacturing\\Requirement\\RequirementFactory' => 'getRequirementFactoryService.php',
            'Rialto\\Manufacturing\\Task\\ProductionTaskFactory' => 'getProductionTaskFactoryService.php',
            'Rialto\\Manufacturing\\WorkOrder\\Issue\\WorkOrderIssuer' => 'getWorkOrderIssuerService.php',
            'Rialto\\Manufacturing\\WorkOrder\\TransferEventListener' => 'getTransferEventListenerService.php',
            'Rialto\\Manufacturing\\WorkOrder\\Web\\WorkOrderController' => 'getWorkOrderControllerService.php',
            'Rialto\\Manufacturing\\WorkOrder\\WorkOrderFactory' => 'getWorkOrderFactoryService.php',
            'Rialto\\Manufacturing\\WorkOrder\\WorkOrderPdfGenerator' => 'getWorkOrderPdfGeneratorService.php',
            'Rialto\\Manufacturing\\WorkType\\ProductLabelPrinter' => 'getProductLabelPrinterService.php',
            'Rialto\\Panelization\\AssetManager' => 'getAssetManagerService.php',
            'Rialto\\Panelization\\IO\\PanelizationStorage' => 'getPanelizationStorageService.php',
            'Rialto\\Panelization\\Layout\\Layout' => 'getLayoutService.php',
            'Rialto\\Panelization\\Orm\\PanelGateway' => 'getPanelGatewayService.php',
            'Rialto\\Panelization\\PanelizedOrderFactory' => 'getPanelizedOrderFactoryService.php',
            'Rialto\\Panelization\\Validator\\PurchasingDataExistsValidator' => 'getPurchasingDataExistsValidator2Service.php',
            'Rialto\\Panelization\\Web\\PanelPdfGenerator' => 'getPanelPdfGeneratorService.php',
            'Rialto\\Payment\\AuthorizeNet' => 'getAuthorizeNetService.php',
            'Rialto\\Payment\\FakeGateway' => 'getFakeGatewayService.php',
            'Rialto\\Payment\\PaymentProcessor' => 'getPaymentProcessor2Service.php',
            'Rialto\\Payment\\Sweep\\CardTransactionSweep' => 'getCardTransactionSweepService.php',
            'Rialto\\PcbNg\\Command\\CreateManufacturedStockItemPcbNgPurchasingDataHandler' => 'getCreateManufacturedStockItemPcbNgPurchasingDataHandlerService.php',
            'Rialto\\PcbNg\\Command\\ProcessPcbNgEmailsHandler' => 'getProcessPcbNgEmailsHandlerService.php',
            'Rialto\\PcbNg\\Service\\GerbersConverter' => 'getGerbersConverterService.php',
            'Rialto\\PcbNg\\Service\\LocationsConverter' => 'getLocationsConverterService.php',
            'Rialto\\PcbNg\\Service\\PcbNgClient' => 'getPcbNgClientService.php',
            'Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer' => 'getPcbNgNotificationEmailerService.php',
            'Rialto\\PcbNg\\Service\\PcbNgSubmitter' => 'getPcbNgSubmitterService.php',
            'Rialto\\PcbNg\\Service\\PickAndPlaceFactory' => 'getPickAndPlaceFactoryService.php',
            'Rialto\\Port\\CommandBus\\CommandBus' => 'getCommandBusService.php',
            'Rialto\\Port\\CommandBus\\CommandQueue' => 'getCommandQueueService.php',
            'Rialto\\Port\\FormatConversion\\PostScriptToPdfConverter' => 'getPostScriptToPdfConverterService.php',
            'Rialto\\Printing\\Job\\PrintQueue' => 'getPrintQueueService.php',
            'Rialto\\Purchasing\\Catalog\\Cli\\PurchasingDataStockLevelRefreshCommand' => 'getPurchasingDataStockLevelRefreshCommandService.php',
            'Rialto\\Purchasing\\Catalog\\Cli\\PurchasingDataSynchronizerCommand' => 'getPurchasingDataSynchronizerCommandService.php',
            'Rialto\\Purchasing\\Catalog\\Cli\\RefreshGeppettoPurchasingDataConsoleCommand' => 'getRefreshGeppettoPurchasingDataConsoleCommandService.php',
            'Rialto\\Purchasing\\Catalog\\Command\\RefreshPurchasingDataStockLevelHandler' => 'getRefreshPurchasingDataStockLevelHandlerService.php',
            'Rialto\\Purchasing\\Catalog\\PurchasingDataSynchronizer' => 'getPurchasingDataSynchronizerService.php',
            'Rialto\\Purchasing\\Catalog\\Remote\\OctopartCatalog' => 'getOctopartCatalogService.php',
            'Rialto\\Purchasing\\Catalog\\Remote\\Web\\RemoteCatalogController' => 'getRemoteCatalogControllerService.php',
            'Rialto\\Purchasing\\Catalog\\Web\\PurchasingDataController' => 'getPurchasingDataControllerService.php',
            'Rialto\\Purchasing\\EmailEventSubscriber' => 'getEmailEventSubscriberService.php',
            'Rialto\\Purchasing\\Invoice\\Command\\UploadSupplierInvoiceFileHandler' => 'getUploadSupplierInvoiceFileHandlerService.php',
            'Rialto\\Purchasing\\Invoice\\Reader\\Email\\AttachmentLocator' => 'getAttachmentLocatorService.php',
            'Rialto\\Purchasing\\Invoice\\Reader\\Email\\AttachmentParser' => 'getAttachmentParserService.php',
            'Rialto\\Purchasing\\Invoice\\Reader\\Email\\SupplierMailbox' => 'getSupplierMailboxService.php',
            'Rialto\\Purchasing\\Invoice\\SupplierInvoiceZipper' => 'getSupplierInvoiceZipperService.php',
            'Rialto\\Purchasing\\Invoice\\Web\\SupplierInvoiceController' => 'getSupplierInvoiceControllerService.php',
            'Rialto\\Purchasing\\Invoice\\Web\\SupplierInvoiceItemApprovalType' => 'getSupplierInvoiceItemApprovalTypeService.php',
            'Rialto\\Purchasing\\LeadTime\\LeadTimeCalculator' => 'getLeadTimeCalculatorService.php',
            'Rialto\\Purchasing\\Manufacturer\\Cli\\BulkPushModuleManufacturersConsoleCommand' => 'getBulkPushModuleManufacturersConsoleCommandService.php',
            'Rialto\\Purchasing\\Manufacturer\\ComplianceFilesystem' => 'getComplianceFilesystemService.php',
            'Rialto\\Purchasing\\Manufacturer\\LogoFilesystem' => 'getLogoFilesystemService.php',
            'Rialto\\Purchasing\\Order\\Attachment\\PurchaseOrderAttachmentGenerator' => 'getPurchaseOrderAttachmentGeneratorService.php',
            'Rialto\\Purchasing\\Order\\Attachment\\PurchaseOrderAttachmentLocator' => 'getPurchaseOrderAttachmentLocatorService.php',
            'Rialto\\Purchasing\\Order\\AutoSendReworkOrderSubscriber' => 'getAutoSendReworkOrderSubscriberService.php',
            'Rialto\\Purchasing\\Order\\Command\\MergePurchaseOrdersHandler' => 'getMergePurchaseOrdersHandlerService.php',
            'Rialto\\Purchasing\\Order\\OrderPdfGenerator' => 'getOrderPdfGeneratorService.php',
            'Rialto\\Purchasing\\Order\\PurchaseOrderFactory' => 'getPurchaseOrderFactoryService.php',
            'Rialto\\Purchasing\\Order\\PurchaseOrderSender' => 'getPurchaseOrderSenderService.php',
            'Rialto\\Purchasing\\Order\\PurchaseOrderVoter' => 'getPurchaseOrderVoterService.php',
            'Rialto\\Purchasing\\Order\\StockItemVoter' => 'getStockItemVoterService.php',
            'Rialto\\Purchasing\\Order\\Web\\CreatePurchaseOrderType' => 'getCreatePurchaseOrderTypeService.php',
            'Rialto\\Purchasing\\Order\\Web\\EditPurchaseOrderType' => 'getEditPurchaseOrderTypeService.php',
            'Rialto\\Purchasing\\Order\\Web\\PurchaseOrderController' => 'getPurchaseOrderControllerService.php',
            'Rialto\\Purchasing\\Producer\\DependencyUpdater' => 'getDependencyUpdaterService.php',
            'Rialto\\Purchasing\\Producer\\StockProducerFactory' => 'getStockProducerFactoryService.php',
            'Rialto\\Purchasing\\Producer\\StockProducerVoter' => 'getStockProducerVoterService.php',
            'Rialto\\Purchasing\\Producer\\Web\\StockProducerType' => 'getStockProducerTypeService.php',
            'Rialto\\Purchasing\\PurchasingErrorHandler' => 'getPurchasingErrorHandlerService.php',
            'Rialto\\Purchasing\\Receiving\\Auth\\CanReceiveIntoValidator' => 'getCanReceiveIntoValidatorService.php',
            'Rialto\\Purchasing\\Receiving\\Auth\\ReceiveIntoVoter' => 'getReceiveIntoVoterService.php',
            'Rialto\\Purchasing\\Receiving\\GoodsReceivedLogger' => 'getGoodsReceivedLoggerService.php',
            'Rialto\\Purchasing\\Receiving\\Notify\\XmppEventSubscriber' => 'getXmppEventSubscriberService.php',
            'Rialto\\Purchasing\\Receiving\\Receiver' => 'getReceiverService.php',
            'Rialto\\Purchasing\\Receiving\\Web\\GoodsReceivedType' => 'getGoodsReceivedTypeService.php',
            'Rialto\\Purchasing\\Supplier\\Attribute\\Web\\SupplierAttributeType' => 'getSupplierAttributeTypeService.php',
            'Rialto\\Purchasing\\Supplier\\SupplierPaymentStatus' => 'getSupplierPaymentStatusService.php',
            'Rialto\\Purchasing\\Supplier\\Web\\ActionsController' => 'getActionsControllerService.php',
            'Rialto\\Sales\\Discount\\DiscountCalculator' => 'getDiscountCalculatorService.php',
            'Rialto\\Sales\\DocumentEventListener' => 'getDocumentEventListenerService.php',
            'Rialto\\Sales\\EmailEventListener' => 'getEmailEventListenerService.php',
            'Rialto\\Sales\\Invoice\\Label\\EciaLabelManager' => 'getEciaLabelManagerService.php',
            'Rialto\\Sales\\Invoice\\SalesInvoiceProcessor' => 'getSalesInvoiceProcessorService.php',
            'Rialto\\Sales\\Order\\Allocation\\AllocationEventListener' => 'getAllocationEventListenerService.php',
            'Rialto\\Sales\\Order\\Allocation\\Command\\CreateStockItemOrderHandler' => 'getCreateStockItemOrderHandlerService.php',
            'Rialto\\Sales\\Order\\CustomerPartNoPopulator' => 'getCustomerPartNoPopulatorService.php',
            'Rialto\\Sales\\Order\\Dates\\TargetShipDateCalculator' => 'getTargetShipDateCalculatorService.php',
            'Rialto\\Sales\\Order\\Dates\\TargetShipDateListener' => 'getTargetShipDateListenerService.php',
            'Rialto\\Sales\\Order\\Dates\\Web\\OrderDateController' => 'getOrderDateControllerService.php',
            'Rialto\\Sales\\Order\\Email\\OrderEmailListener' => 'getOrderEmailListenerService.php',
            'Rialto\\Sales\\Order\\Email\\OrderToEmailFilter' => 'getOrderToEmailFilterService.php',
            'Rialto\\Sales\\Order\\Import\\OrderImporter' => 'getOrderImporterService.php',
            'Rialto\\Sales\\Order\\OrderUpdateListener' => 'getOrderUpdateListenerService.php',
            'Rialto\\Sales\\Order\\SalesOrderPaymentProcessor' => 'getSalesOrderPaymentProcessorService.php',
            'Rialto\\Sales\\Order\\SoftwareInvoicer' => 'getSoftwareInvoicerService.php',
            'Rialto\\Sales\\Returns\\Disposition\\SalesReturnDisposition' => 'getSalesReturnDispositionService.php',
            'Rialto\\Sales\\Returns\\Receipt\\SalesReturnReceiver' => 'getSalesReturnReceiverService.php',
            'Rialto\\Sales\\SalesLogger' => 'getSalesLoggerService.php',
            'Rialto\\Sales\\SalesPdfGenerator' => 'getSalesPdfGeneratorService.php',
            'Rialto\\Sales\\SalesPrintManager' => 'getSalesPrintManagerService.php',
            'Rialto\\Sales\\Shipping\\ApproveToShipEventListener' => 'getApproveToShipEventListenerService.php',
            'Rialto\\Sales\\Shipping\\SalesOrderShippingApproval' => 'getSalesOrderShippingApprovalService.php',
            'Rialto\\Security\\Firewall\\ByUsernameProvider' => 'getByUsernameProviderService.php',
            'Rialto\\Security\\Firewall\\ByUuidProvider' => 'getByUuidProviderService.php',
            'Rialto\\Security\\Firewall\\UsernameNotFoundExceptionHandler' => 'getUsernameNotFoundExceptionHandlerService.php',
            'Rialto\\Security\\User\\LastLoginUpdater' => 'getLastLoginUpdaterService.php',
            'Rialto\\Security\\User\\UserVoter' => 'getUserVoterService.php',
            'Rialto\\Security\\User\\Web\\UserType' => 'getUserTypeService.php',
            'Rialto\\Shipping\\Export\\AllowedCountryValidator' => 'getAllowedCountryValidatorService.php',
            'Rialto\\Shipping\\Export\\DeniedPartyScreener' => 'getDeniedPartyScreenerService.php',
            'Rialto\\Shipping\\Shipment\\ShipmentFactory' => 'getShipmentFactoryService.php',
            'Rialto\\Shipping\\Shipment\\Web\\ShipmentOptionsType' => 'getShipmentOptionsTypeService.php',
            'Rialto\\Shopify\\Order\\FulfillmentListener' => 'getFulfillmentListenerService.php',
            'Rialto\\Shopify\\Order\\OrderClosedListener' => 'getOrderClosedListener2Service.php',
            'Rialto\\Shopify\\Order\\PaymentProcessor' => 'getPaymentProcessor3Service.php',
            'Rialto\\Shopify\\Webhook\\ShopifyUserProvider' => 'getShopifyUserProviderService.php',
            'Rialto\\Shopify\\Webhook\\WebhookAuthenticator' => 'getWebhookAuthenticatorService.php',
            'Rialto\\Stock\\Bin\\Label\\BinLabelListener' => 'getBinLabelListenerService.php',
            'Rialto\\Stock\\Bin\\Label\\BinLabelPrintQueue' => 'getBinLabelPrintQueueService.php',
            'Rialto\\Stock\\Bin\\Label\\Web\\LabelController' => 'getLabelControllerService.php',
            'Rialto\\Stock\\Bin\\StockBinSplitter' => 'getStockBinSplitterService.php',
            'Rialto\\Stock\\Bin\\StockBinUpdateListener' => 'getStockBinUpdateListenerService.php',
            'Rialto\\Stock\\Bin\\StockBinVoter' => 'getStockBinVoterService.php',
            'Rialto\\Stock\\Bin\\Web\\BinUpdateAllocType' => 'getBinUpdateAllocTypeService.php',
            'Rialto\\Stock\\Bin\\Web\\StockAdjustmentType' => 'getStockAdjustmentTypeService.php',
            'Rialto\\Stock\\Bin\\Web\\StockBinController' => 'getStockBinControllerService.php',
            'Rialto\\Stock\\Category\\CategoryChange' => 'getCategoryChangeService.php',
            'Rialto\\Stock\\Cost\\StandardCostUpdater' => 'getStandardCostUpdaterService.php',
            'Rialto\\Stock\\Count\\StockCountVoter' => 'getStockCountVoterService.php',
            'Rialto\\Stock\\Count\\Web\\CsvStockCountFlow' => 'getCsvStockCountFlowService.php',
            'Rialto\\Stock\\EmailEventListener' => 'getEmailEventListener2Service.php',
            'Rialto\\Stock\\Item\\BatchStockUpdater' => 'getBatchStockUpdaterService.php',
            'Rialto\\Stock\\Item\\Cli\\StockLevelRefreshCommand' => 'getStockLevelRefreshCommandService.php',
            'Rialto\\Stock\\Item\\Command\\RefreshStockLevelHandler' => 'getRefreshStockLevelHandlerService.php',
            'Rialto\\Stock\\Item\\NewSkuValidator' => 'getNewSkuValidatorService.php',
            'Rialto\\Stock\\Item\\StockItemDeleteService' => 'getStockItemDeleteServiceService.php',
            'Rialto\\Stock\\Item\\StockItemFactory' => 'getStockItemFactoryService.php',
            'Rialto\\Stock\\Item\\Version\\Web\\ItemVersionSelectorType' => 'getItemVersionSelectorTypeService.php',
            'Rialto\\Stock\\Item\\Web\\ActionsController' => 'getActionsController2Service.php',
            'Rialto\\Stock\\Item\\Web\\EditType' => 'getEditTypeService.php',
            'Rialto\\Stock\\Item\\Web\\StockItemAttributeType' => 'getStockItemAttributeTypeService.php',
            'Rialto\\Stock\\Item\\Web\\StockItemTemplateType' => 'getStockItemTemplateTypeService.php',
            'Rialto\\Stock\\Level\\StockLevelService' => 'getStockLevelServiceService.php',
            'Rialto\\Stock\\Level\\StockLevelSynchronizer' => 'getStockLevelSynchronizerService.php',
            'Rialto\\Stock\\Publication\\PublicationFilesystem' => 'getPublicationFilesystemService.php',
            'Rialto\\Stock\\Publication\\PublicationPrintManager' => 'getPublicationPrintManagerService.php',
            'Rialto\\Stock\\Returns\\Problem\\ReturnedItemResolver' => 'getReturnedItemResolverService.php',
            'Rialto\\Stock\\Returns\\ReturnedItemService' => 'getReturnedItemServiceService.php',
            'Rialto\\Stock\\Returns\\Web\\ReturnedItemsFlow' => 'getReturnedItemsFlowService.php',
            'Rialto\\Stock\\Shelf\\Position\\AssignmentListener' => 'getAssignmentListenerService.php',
            'Rialto\\Stock\\Shelf\\Position\\PositionAssigner' => 'getPositionAssignerService.php',
            'Rialto\\Stock\\Transfer\\BinEventListener' => 'getBinEventListenerService.php',
            'Rialto\\Stock\\Transfer\\TransferReceiver' => 'getTransferReceiverService.php',
            'Rialto\\Stock\\Transfer\\TransferService' => 'getTransferServiceService.php',
            'Rialto\\Summary\\Menu\\SummaryVoter' => 'getSummaryVoterService.php',
            'Rialto\\Supplier\\Allocation\\Web\\BinAllocationController' => 'getBinAllocationControllerService.php',
            'Rialto\\Supplier\\Logger' => 'getLoggerService.php',
            'Rialto\\Supplier\\Order\\Email\\EmailSubscriber' => 'getEmailSubscriberService.php',
            'Rialto\\Supplier\\Order\\Web\\TrackingFacades\\SupplierInvoiceTrackingFacadesFactory' => 'getSupplierInvoiceTrackingFacadesFactoryService.php',
            'Rialto\\Supplier\\Order\\Web\\WorkOrderController' => 'getWorkOrderController2Service.php',
            'Rialto\\Supplier\\SupplierVoter' => 'getSupplierVoterService.php',
            'Rialto\\Task\\TaskVoter' => 'getTaskVoterService.php',
            'Rialto\\Tax\\TaxLookup' => 'getTaxLookupService.php',
            'Rialto\\Ups\\Invoice\\InvoiceLoader' => 'getInvoiceLoaderService.php',
            'Rialto\\Ups\\Shipping\\Label\\ShippingLabelListener' => 'getShippingLabelListenerService.php',
            'Rialto\\Ups\\Shipping\\Webservice\\UpsApiService' => 'getUpsApiServiceService.php',
            'Rialto\\Ups\\TrackingRecord\\Cli\\PollTrackingNumbersCommand' => 'getPollTrackingNumbersCommandService.php',
            'Rialto\\Ups\\TrackingRecord\\Cli\\UpdatePOTrackingRecordsCommand' => 'getUpdatePOTrackingRecordsCommandService.php',
            'Rialto\\Ups\\TrackingRecord\\Cli\\UpdateSalesTrackingRecordsCommand' => 'getUpdateSalesTrackingRecordsCommandService.php',
            'Rialto\\Ups\\TrackingRecord\\Command\\UpdateTrackingRecordHandler' => 'getUpdateTrackingRecordHandlerService.php',
            'Rialto\\Web\\Form\\JsEntityType' => 'getJsEntityTypeService.php',
            'Rialto\\Web\\Form\\NumberTypeExtension' => 'getNumberTypeExtensionService.php',
            'Rialto\\Web\\Form\\TextEntityType' => 'getTextEntityTypeService.php',
            'Rialto\\Web\\Form\\Validator' => 'getValidator2Service.php',
            'Rialto\\Wordpress\\ChangeNoticeListener' => 'getChangeNoticeListenerService.php',
            'Symfony\\Bundle\\FrameworkBundle\\Controller\\RedirectController' => 'getRedirectControllerService.php',
            'Symfony\\Bundle\\FrameworkBundle\\Controller\\TemplateController' => 'getTemplateControllerService.php',
            'annotations.cache' => 'getAnnotations_CacheService.php',
            'annotations.cache_warmer' => 'getAnnotations_CacheWarmerService.php',
            'argument_resolver.default' => 'getArgumentResolver_DefaultService.php',
            'argument_resolver.request' => 'getArgumentResolver_RequestService.php',
            'argument_resolver.request_attribute' => 'getArgumentResolver_RequestAttributeService.php',
            'argument_resolver.service' => 'getArgumentResolver_ServiceService.php',
            'argument_resolver.session' => 'getArgumentResolver_SessionService.php',
            'argument_resolver.variadic' => 'getArgumentResolver_VariadicService.php',
            'autowired.Rialto\\Accounting\\Bank\\Transfer\\BankTransfer' => 'getBankTransferService.php',
            'autowired.Rialto\\Purchasing\\Invoice\\SupplierInvoice' => 'getSupplierInvoiceService.php',
            'autowired.Rialto\\Purchasing\\Invoice\\SupplierInvoiceItem' => 'getSupplierInvoiceItemService.php',
            'autowired.Rialto\\Purchasing\\Supplier\\Supplier' => 'getSupplierService.php',
            'autowired.Rialto\\Stock\\Item\\ManufacturedStockItem' => 'getManufacturedStockItemService.php',
            'autowired.Rialto\\Stock\\Item\\PurchasedStockItem' => 'getPurchasedStockItemService.php',
            'cache.annotations' => 'getCache_AnnotationsService.php',
            'cache.app' => 'getCache_AppService.php',
            'cache.default_clearer' => 'getCache_DefaultClearerService.php',
            'cache.global_clearer' => 'getCache_GlobalClearerService.php',
            'cache.serializer' => 'getCache_SerializerService.php',
            'cache.system' => 'getCache_SystemService.php',
            'cache.system_clearer' => 'getCache_SystemClearerService.php',
            'cache.validator' => 'getCache_ValidatorService.php',
            'cache.webpack_encore' => 'getCache_WebpackEncoreService.php',
            'cache_clearer' => 'getCacheClearerService.php',
            'cache_warmer' => 'getCacheWarmerService.php',
            'config.resource.self_checking_resource_checker' => 'getConfig_Resource_SelfCheckingResourceCheckerService.php',
            'console.command.about' => 'getConsole_Command_AboutService.php',
            'console.command.assets_install' => 'getConsole_Command_AssetsInstallService.php',
            'console.command.cache_clear' => 'getConsole_Command_CacheClearService.php',
            'console.command.cache_pool_clear' => 'getConsole_Command_CachePoolClearService.php',
            'console.command.cache_pool_prune' => 'getConsole_Command_CachePoolPruneService.php',
            'console.command.cache_warmup' => 'getConsole_Command_CacheWarmupService.php',
            'console.command.config_debug' => 'getConsole_Command_ConfigDebugService.php',
            'console.command.config_dump_reference' => 'getConsole_Command_ConfigDumpReferenceService.php',
            'console.command.container_debug' => 'getConsole_Command_ContainerDebugService.php',
            'console.command.debug_autowiring' => 'getConsole_Command_DebugAutowiringService.php',
            'console.command.event_dispatcher_debug' => 'getConsole_Command_EventDispatcherDebugService.php',
            'console.command.form_debug' => 'getConsole_Command_FormDebugService.php',
            'console.command.gumstix_ssobundle_command_setupcommand' => 'getConsole_Command_GumstixSsobundleCommandSetupcommandService.php',
            'console.command.rialto_accounting_paymenttransaction_cli_recalculatesettled' => 'getConsole_Command_RialtoAccountingPaymenttransactionCliRecalculatesettledService.php',
            'console.command.rialto_allocation_cli_deleteinvalidallocationscommand' => 'getConsole_Command_RialtoAllocationCliDeleteinvalidallocationscommandService.php',
            'console.command.rialto_allocation_cli_deletestockbinallocationscommand' => 'getConsole_Command_RialtoAllocationCliDeletestockbinallocationscommandService.php',
            'console.command.rialto_logging_cli_recreatemongologscommand' => 'getConsole_Command_RialtoLoggingCliRecreatemongologscommandService.php',
            'console.command.rialto_magento2_order_cli_syncorderscommand' => 'getConsole_Command_RialtoMagento2OrderCliSyncorderscommandService.php',
            'console.command.rialto_magento2_stock_cli_syncstocklevelscommand' => 'getConsole_Command_RialtoMagento2StockCliSyncstocklevelscommandService.php',
            'console.command.rialto_manufacturing_customization_cli_validatesubstitutionscommand' => 'getConsole_Command_RialtoManufacturingCustomizationCliValidatesubstitutionscommandService.php',
            'console.command.rialto_manufacturing_kit_reminder_sendemailcommand' => 'getConsole_Command_RialtoManufacturingKitReminderSendemailcommandService.php',
            'console.command.rialto_manufacturing_task_cli_jobscommand' => 'getConsole_Command_RialtoManufacturingTaskCliJobscommandService.php',
            'console.command.rialto_manufacturing_task_cli_productiontaskremindercommand' => 'getConsole_Command_RialtoManufacturingTaskCliProductiontaskremindercommandService.php',
            'console.command.rialto_manufacturing_task_cli_refreshproductiontaskscommand' => 'getConsole_Command_RialtoManufacturingTaskCliRefreshproductiontaskscommandService.php',
            'console.command.rialto_manufacturing_task_cli_taskscommand' => 'getConsole_Command_RialtoManufacturingTaskCliTaskscommandService.php',
            'console.command.rialto_manufacturing_workorder_cli_autobuildcommand' => 'getConsole_Command_RialtoManufacturingWorkorderCliAutobuildcommandService.php',
            'console.command.rialto_payment_sweep_cli_sweepcardtransactionscommand' => 'getConsole_Command_RialtoPaymentSweepCliSweepcardtransactionscommandService.php',
            'console.command.rialto_port_commandbus_handlecommandconsolecommand' => 'getConsole_Command_RialtoPortCommandbusHandlecommandconsolecommandService.php',
            'console.command.rialto_printing_job_cli_deletecompletedprintjobs' => 'getConsole_Command_RialtoPrintingJobCliDeletecompletedprintjobsService.php',
            'console.command.rialto_printing_job_cli_flushprintqueue' => 'getConsole_Command_RialtoPrintingJobCliFlushprintqueueService.php',
            'console.command.rialto_printing_printer_cli_devprintserver' => 'getConsole_Command_RialtoPrintingPrinterCliDevprintserverService.php',
            'console.command.rialto_purchasing_invoice_cli_finduninvoicedorders' => 'getConsole_Command_RialtoPurchasingInvoiceCliFinduninvoicedordersService.php',
            'console.command.rialto_purchasing_invoice_reader_email_cli_autoimportinvoices' => 'getConsole_Command_RialtoPurchasingInvoiceReaderEmailCliAutoimportinvoicesService.php',
            'console.command.rialto_purchasing_order_cli_autoordercommand' => 'getConsole_Command_RialtoPurchasingOrderCliAutoordercommandService.php',
            'console.command.rialto_purchasing_receiving_notify_testxmppcommand' => 'getConsole_Command_RialtoPurchasingReceivingNotifyTestxmppcommandService.php',
            'console.command.rialto_purchasing_recurring_cli_autoinvoicecommand' => 'getConsole_Command_RialtoPurchasingRecurringCliAutoinvoicecommandService.php',
            'console.command.rialto_sales_order_dates_inittargetdatecommand' => 'getConsole_Command_RialtoSalesOrderDatesInittargetdatecommandService.php',
            'console.command.rialto_security_user_cli_adduuidcommand' => 'getConsole_Command_RialtoSecurityUserCliAdduuidcommandService.php',
            'console.command.rialto_security_user_cli_createusercommand' => 'getConsole_Command_RialtoSecurityUserCliCreateusercommandService.php',
            'console.command.rialto_security_user_cli_promoteusercommand' => 'getConsole_Command_RialtoSecurityUserCliPromoteusercommandService.php',
            'console.command.rialto_shopify_webhook_cli_webhookcustomcommand' => 'getConsole_Command_RialtoShopifyWebhookCliWebhookcustomcommandService.php',
            'console.command.rialto_stock_item_cli_bulksetdefaultworkordercommand' => 'getConsole_Command_RialtoStockItemCliBulksetdefaultworkordercommandService.php',
            'console.command.rialto_stock_level_cli_stocklevelsynccommand' => 'getConsole_Command_RialtoStockLevelCliStocklevelsynccommandService.php',
            'console.command.rialto_stock_returns_cli_generatemissingadjustmentglrecordscommand' => 'getConsole_Command_RialtoStockReturnsCliGeneratemissingadjustmentglrecordscommandService.php',
            'console.command.rialto_tax_regime_cli_loadtaxregimescommand' => 'getConsole_Command_RialtoTaxRegimeCliLoadtaxregimescommandService.php',
            'console.command.router_debug' => 'getConsole_Command_RouterDebugService.php',
            'console.command.router_match' => 'getConsole_Command_RouterMatchService.php',
            'console.command.translation_debug' => 'getConsole_Command_TranslationDebugService.php',
            'console.command.translation_update' => 'getConsole_Command_TranslationUpdateService.php',
            'console.command.xliff_lint' => 'getConsole_Command_XliffLintService.php',
            'console.command.yaml_lint' => 'getConsole_Command_YamlLintService.php',
            'console.command_loader' => 'getConsole_CommandLoaderService.php',
            'console.error_listener' => 'getConsole_ErrorListenerService.php',
            'craue.form.flow' => 'getCraue_Form_FlowService.php',
            'craue.form.flow.data_manager' => 'getCraue_Form_Flow_DataManagerService.php',
            'craue.form.flow.event_listener.flow_expired' => 'getCraue_Form_Flow_EventListener_FlowExpiredService.php',
            'craue.form.flow.event_listener.previous_step_invalid' => 'getCraue_Form_Flow_EventListener_PreviousStepInvalidService.php',
            'craue.form.flow.form_extension' => 'getCraue_Form_Flow_FormExtensionService.php',
            'craue.form.flow.hidden_field_extension' => 'getCraue_Form_Flow_HiddenFieldExtensionService.php',
            'craue.form.flow.storage' => 'getCraue_Form_Flow_StorageService.php',
            'debug.file_link_formatter.url_format' => 'getDebug_FileLinkFormatter_UrlFormatService.php',
            'dependency_injection.config.container_parameters_resource_checker' => 'getDependencyInjection_Config_ContainerParametersResourceCheckerService.php',
            'deprecated.form.registry' => 'getDeprecated_Form_RegistryService.php',
            'deprecated.form.registry.csrf' => 'getDeprecated_Form_Registry_CsrfService.php',
            'doctrine.cache_clear_metadata_command' => 'getDoctrine_CacheClearMetadataCommandService.php',
            'doctrine.cache_clear_query_cache_command' => 'getDoctrine_CacheClearQueryCacheCommandService.php',
            'doctrine.cache_clear_result_command' => 'getDoctrine_CacheClearResultCommandService.php',
            'doctrine.cache_collection_region_command' => 'getDoctrine_CacheCollectionRegionCommandService.php',
            'doctrine.clear_entity_region_command' => 'getDoctrine_ClearEntityRegionCommandService.php',
            'doctrine.clear_query_region_command' => 'getDoctrine_ClearQueryRegionCommandService.php',
            'doctrine.database_create_command' => 'getDoctrine_DatabaseCreateCommandService.php',
            'doctrine.database_drop_command' => 'getDoctrine_DatabaseDropCommandService.php',
            'doctrine.database_import_command' => 'getDoctrine_DatabaseImportCommandService.php',
            'doctrine.ensure_production_settings_command' => 'getDoctrine_EnsureProductionSettingsCommandService.php',
            'doctrine.generate_entities_command' => 'getDoctrine_GenerateEntitiesCommandService.php',
            'doctrine.mapping_convert_command' => 'getDoctrine_MappingConvertCommandService.php',
            'doctrine.mapping_import_command' => 'getDoctrine_MappingImportCommandService.php',
            'doctrine.mapping_info_command' => 'getDoctrine_MappingInfoCommandService.php',
            'doctrine.orm.default_entity_manager.property_info_extractor' => 'getDoctrine_Orm_DefaultEntityManager_PropertyInfoExtractorService.php',
            'doctrine.orm.proxy_cache_warmer' => 'getDoctrine_Orm_ProxyCacheWarmerService.php',
            'doctrine.orm.validator.unique' => 'getDoctrine_Orm_Validator_UniqueService.php',
            'doctrine.query_dql_command' => 'getDoctrine_QueryDqlCommandService.php',
            'doctrine.query_sql_command' => 'getDoctrine_QuerySqlCommandService.php',
            'doctrine.schema_create_command' => 'getDoctrine_SchemaCreateCommandService.php',
            'doctrine.schema_drop_command' => 'getDoctrine_SchemaDropCommandService.php',
            'doctrine.schema_update_command' => 'getDoctrine_SchemaUpdateCommandService.php',
            'doctrine.schema_validate_command' => 'getDoctrine_SchemaValidateCommandService.php',
            'doctrine_cache.contains_command' => 'getDoctrineCache_ContainsCommandService.php',
            'doctrine_cache.delete_command' => 'getDoctrineCache_DeleteCommandService.php',
            'doctrine_cache.flush_command' => 'getDoctrineCache_FlushCommandService.php',
            'doctrine_cache.stats_command' => 'getDoctrineCache_StatsCommandService.php',
            'doctrine_migrations.diff_command' => 'getDoctrineMigrations_DiffCommandService.php',
            'doctrine_migrations.execute_command' => 'getDoctrineMigrations_ExecuteCommandService.php',
            'doctrine_migrations.generate_command' => 'getDoctrineMigrations_GenerateCommandService.php',
            'doctrine_migrations.latest_command' => 'getDoctrineMigrations_LatestCommandService.php',
            'doctrine_migrations.migrate_command' => 'getDoctrineMigrations_MigrateCommandService.php',
            'doctrine_migrations.status_command' => 'getDoctrineMigrations_StatusCommandService.php',
            'doctrine_migrations.version_command' => 'getDoctrineMigrations_VersionCommandService.php',
            'easyadmin.autocomplete' => 'getEasyadmin_AutocompleteService.php',
            'easyadmin.form.guesser.missing_doctrine_orm_type_guesser' => 'getEasyadmin_Form_Guesser_MissingDoctrineOrmTypeGuesserService.php',
            'easyadmin.form.type' => 'getEasyadmin_Form_TypeService.php',
            'easyadmin.form.type.autocomplete' => 'getEasyadmin_Form_Type_AutocompleteService.php',
            'easyadmin.form.type.divider' => 'getEasyadmin_Form_Type_DividerService.php',
            'easyadmin.form.type.extension' => 'getEasyadmin_Form_Type_ExtensionService.php',
            'easyadmin.form.type.group' => 'getEasyadmin_Form_Type_GroupService.php',
            'easyadmin.form.type.section' => 'getEasyadmin_Form_Type_SectionService.php',
            'easyadmin.listener.request_post_initialize' => 'getEasyadmin_Listener_RequestPostInitializeService.php',
            'easyadmin.paginator' => 'getEasyadmin_PaginatorService.php',
            'easyadmin.query_builder' => 'getEasyadmin_QueryBuilderService.php',
            'filesystem' => 'getFilesystemService.php',
            'form.factory' => 'getForm_FactoryService.php',
            'form.server_params' => 'getForm_ServerParamsService.php',
            'form.type.birthday' => 'getForm_Type_BirthdayService.php',
            'form.type.button' => 'getForm_Type_ButtonService.php',
            'form.type.checkbox' => 'getForm_Type_CheckboxService.php',
            'form.type.choice' => 'getForm_Type_ChoiceService.php',
            'form.type.collection' => 'getForm_Type_CollectionService.php',
            'form.type.country' => 'getForm_Type_CountryService.php',
            'form.type.currency' => 'getForm_Type_CurrencyService.php',
            'form.type.date' => 'getForm_Type_DateService.php',
            'form.type.datetime' => 'getForm_Type_DatetimeService.php',
            'form.type.email' => 'getForm_Type_EmailService.php',
            'form.type.entity' => 'getForm_Type_EntityService.php',
            'form.type.file' => 'getForm_Type_FileService.php',
            'form.type.form' => 'getForm_Type_FormService.php',
            'form.type.hidden' => 'getForm_Type_HiddenService.php',
            'form.type.integer' => 'getForm_Type_IntegerService.php',
            'form.type.language' => 'getForm_Type_LanguageService.php',
            'form.type.locale' => 'getForm_Type_LocaleService.php',
            'form.type.money' => 'getForm_Type_MoneyService.php',
            'form.type.number' => 'getForm_Type_NumberService.php',
            'form.type.password' => 'getForm_Type_PasswordService.php',
            'form.type.percent' => 'getForm_Type_PercentService.php',
            'form.type.radio' => 'getForm_Type_RadioService.php',
            'form.type.range' => 'getForm_Type_RangeService.php',
            'form.type.repeated' => 'getForm_Type_RepeatedService.php',
            'form.type.reset' => 'getForm_Type_ResetService.php',
            'form.type.search' => 'getForm_Type_SearchService.php',
            'form.type.submit' => 'getForm_Type_SubmitService.php',
            'form.type.text' => 'getForm_Type_TextService.php',
            'form.type.textarea' => 'getForm_Type_TextareaService.php',
            'form.type.time' => 'getForm_Type_TimeService.php',
            'form.type.timezone' => 'getForm_Type_TimezoneService.php',
            'form.type.url' => 'getForm_Type_UrlService.php',
            'form.type_extension.csrf' => 'getForm_TypeExtension_CsrfService.php',
            'form.type_extension.form.http_foundation' => 'getForm_TypeExtension_Form_HttpFoundationService.php',
            'form.type_extension.form.transformation_failure_handling' => 'getForm_TypeExtension_Form_TransformationFailureHandlingService.php',
            'form.type_extension.form.validator' => 'getForm_TypeExtension_Form_ValidatorService.php',
            'form.type_extension.repeated.validator' => 'getForm_TypeExtension_Repeated_ValidatorService.php',
            'form.type_extension.submit.validator' => 'getForm_TypeExtension_Submit_ValidatorService.php',
            'form.type_extension.upload.validator' => 'getForm_TypeExtension_Upload_ValidatorService.php',
            'form.type_guesser.doctrine' => 'getForm_TypeGuesser_DoctrineService.php',
            'form.type_guesser.validator' => 'getForm_TypeGuesser_ValidatorService.php',
            'fos_js_routing.controller' => 'getFosJsRouting_ControllerService.php',
            'fos_js_routing.dump_command' => 'getFosJsRouting_DumpCommandService.php',
            'fos_js_routing.extractor' => 'getFosJsRouting_ExtractorService.php',
            'fos_js_routing.router_debug_exposed_command' => 'getFosJsRouting_RouterDebugExposedCommandService.php',
            'fos_js_routing.serializer' => 'getFosJsRouting_SerializerService.php',
            'fos_rest.decoder.json' => 'getFosRest_Decoder_JsonService.php',
            'fos_rest.decoder.jsontoform' => 'getFosRest_Decoder_JsontoformService.php',
            'fos_rest.decoder.xml' => 'getFosRest_Decoder_XmlService.php',
            'fos_rest.exception.codes_map' => 'getFosRest_Exception_CodesMapService.php',
            'fos_rest.exception.controller' => 'getFosRest_Exception_ControllerService.php',
            'fos_rest.exception.messages_map' => 'getFosRest_Exception_MessagesMapService.php',
            'fos_rest.exception.twig_controller' => 'getFosRest_Exception_TwigControllerService.php',
            'fos_rest.exception_listener' => 'getFosRest_ExceptionListenerService.php',
            'fos_rest.inflector' => 'getFosRest_InflectorService.php',
            'fos_rest.normalizer.camel_keys' => 'getFosRest_Normalizer_CamelKeysService.php',
            'fos_rest.normalizer.camel_keys_with_leading_underscore' => 'getFosRest_Normalizer_CamelKeysWithLeadingUnderscoreService.php',
            'fos_rest.request.param_fetcher' => 'getFosRest_Request_ParamFetcherService.php',
            'fos_rest.request.param_fetcher.reader' => 'getFosRest_Request_ParamFetcher_ReaderService.php',
            'fos_rest.serializer' => 'getFosRest_SerializerService.php',
            'fos_rest.serializer.exception_normalizer.jms' => 'getFosRest_Serializer_ExceptionNormalizer_JmsService.php',
            'fos_rest.serializer.form_error_handler' => 'getFosRest_Serializer_FormErrorHandlerService.php',
            'fos_rest.serializer.jms_handler_registry.inner' => 'getFosRest_Serializer_JmsHandlerRegistry_InnerService.php',
            'fos_rest.view_handler' => 'getFosRest_ViewHandlerService.php',
            'fos_rest.view_response_listener' => 'getFosRest_ViewResponseListenerService.php',
            'fragment.handler' => 'getFragment_HandlerService.php',
            'fragment.renderer.inline' => 'getFragment_Renderer_InlineService.php',
            'gumstix_sso.credential_storage_database' => 'getGumstixSso_CredentialStorageDatabaseService.php',
            'jms_job_queue.command.clean_up' => 'getJmsJobQueue_Command_CleanUpService.php',
            'jms_job_queue.command.mark_job_incomplete' => 'getJmsJobQueue_Command_MarkJobIncompleteService.php',
            'jms_job_queue.command.run' => 'getJmsJobQueue_Command_RunService.php',
            'jms_job_queue.command.schedule' => 'getJmsJobQueue_Command_ScheduleService.php',
            'jms_job_queue.entity.many_to_any_listener' => 'getJmsJobQueue_Entity_ManyToAnyListenerService.php',
            'jms_job_queue.entity.statistics_listener' => 'getJmsJobQueue_Entity_StatisticsListenerService.php',
            'jms_job_queue.job_manager' => 'getJmsJobQueue_JobManagerService.php',
            'jms_job_queue.retry_scheduler' => 'getJmsJobQueue_RetrySchedulerService.php',
            'jms_serializer.accessor_strategy' => 'getJmsSerializer_AccessorStrategyService.php',
            'jms_serializer.array_collection_handler' => 'getJmsSerializer_ArrayCollectionHandlerService.php',
            'jms_serializer.constraint_violation_handler' => 'getJmsSerializer_ConstraintViolationHandlerService.php',
            'jms_serializer.datetime_handler' => 'getJmsSerializer_DatetimeHandlerService.php',
            'jms_serializer.deserialization_context_factory' => 'getJmsSerializer_DeserializationContextFactoryService.php',
            'jms_serializer.doctrine_proxy_subscriber' => 'getJmsSerializer_DoctrineProxySubscriberService.php',
            'jms_serializer.expression_evaluator' => 'getJmsSerializer_ExpressionEvaluatorService.php',
            'jms_serializer.handler_registry' => 'getJmsSerializer_HandlerRegistryService.php',
            'jms_serializer.json_deserialization_visitor' => 'getJmsSerializer_JsonDeserializationVisitorService.php',
            'jms_serializer.json_serialization_visitor' => 'getJmsSerializer_JsonSerializationVisitorService.php',
            'jms_serializer.metadata_driver' => 'getJmsSerializer_MetadataDriverService.php',
            'jms_serializer.object_constructor' => 'getJmsSerializer_ObjectConstructorService.php',
            'jms_serializer.php_collection_handler' => 'getJmsSerializer_PhpCollectionHandlerService.php',
            'jms_serializer.serialization_context_factory' => 'getJmsSerializer_SerializationContextFactoryService.php',
            'jms_serializer.serialized_name_annotation_strategy' => 'getJmsSerializer_SerializedNameAnnotationStrategyService.php',
            'jms_serializer.stopwatch_subscriber' => 'getJmsSerializer_StopwatchSubscriberService.php',
            'jms_serializer.templating.helper.serializer' => 'getJmsSerializer_Templating_Helper_SerializerService.php',
            'jms_serializer.twig_extension.serializer_runtime_helper' => 'getJmsSerializer_TwigExtension_SerializerRuntimeHelperService.php',
            'jms_serializer.unserialize_object_constructor' => 'getJmsSerializer_UnserializeObjectConstructorService.php',
            'jms_serializer.xml_deserialization_visitor' => 'getJmsSerializer_XmlDeserializationVisitorService.php',
            'jms_serializer.xml_serialization_visitor' => 'getJmsSerializer_XmlSerializationVisitorService.php',
            'jms_serializer.yaml_serialization_visitor' => 'getJmsSerializer_YamlSerializationVisitorService.php',
            'kernel.class_cache.cache_warmer' => 'getKernel_ClassCache_CacheWarmerService.php',
            'logger' => 'getLogger2Service.php',
            'monolog.handler.automation' => 'getMonolog_Handler_AutomationService.php',
            'monolog.handler.email' => 'getMonolog_Handler_EmailService.php',
            'monolog.handler.flash' => 'getMonolog_Handler_FlashService.php',
            'monolog.handler.null_internal' => 'getMonolog_Handler_NullInternalService.php',
            'monolog.handler.production' => 'getMonolog_Handler_ProductionService.php',
            'monolog.handler.ups' => 'getMonolog_Handler_UpsService.php',
            'monolog.logger.automation' => 'getMonolog_Logger_AutomationService.php',
            'monolog.logger.cache' => 'getMonolog_Logger_CacheService.php',
            'monolog.logger.command_bus' => 'getMonolog_Logger_CommandBusService.php',
            'monolog.logger.console' => 'getMonolog_Logger_ConsoleService.php',
            'monolog.logger.email' => 'getMonolog_Logger_EmailService.php',
            'monolog.logger.flash' => 'getMonolog_Logger_FlashService.php',
            'monolog.logger.manufacturing' => 'getMonolog_Logger_ManufacturingService.php',
            'monolog.logger.receiving' => 'getMonolog_Logger_ReceivingService.php',
            'monolog.logger.sales' => 'getMonolog_Logger_SalesService.php',
            'monolog.logger.security' => 'getMonolog_Logger_SecurityService.php',
            'monolog.logger.supplier' => 'getMonolog_Logger_SupplierService.php',
            'monolog.logger.templating' => 'getMonolog_Logger_TemplatingService.php',
            'monolog.logger.ups' => 'getMonolog_Logger_UpsService.php',
            'router.cache_warmer' => 'getRouter_CacheWarmerService.php',
            'routing.loader' => 'getRouting_LoaderService.php',
            'security.access.authenticated_voter' => 'getSecurity_Access_AuthenticatedVoterService.php',
            'security.access.expression_voter' => 'getSecurity_Access_ExpressionVoterService.php',
            'security.access.role_hierarchy_voter' => 'getSecurity_Access_RoleHierarchyVoterService.php',
            'security.access_listener' => 'getSecurity_AccessListenerService.php',
            'security.access_map' => 'getSecurity_AccessMapService.php',
            'security.authentication.guard_handler' => 'getSecurity_Authentication_GuardHandlerService.php',
            'security.authentication.listener.guard.api' => 'getSecurity_Authentication_Listener_Guard_ApiService.php',
            'security.authentication.listener.guard.main' => 'getSecurity_Authentication_Listener_Guard_MainService.php',
            'security.authentication.listener.simple_preauth.magento2_oauth_callback' => 'getSecurity_Authentication_Listener_SimplePreauth_Magento2OauthCallbackService.php',
            'security.authentication.listener.simple_preauth.shopify_webhook' => 'getSecurity_Authentication_Listener_SimplePreauth_ShopifyWebhookService.php',
            'security.authentication.provider.guard.api' => 'getSecurity_Authentication_Provider_Guard_ApiService.php',
            'security.authentication.provider.guard.main' => 'getSecurity_Authentication_Provider_Guard_MainService.php',
            'security.authentication.provider.simple_preauth.magento2_oauth_callback' => 'getSecurity_Authentication_Provider_SimplePreauth_Magento2OauthCallbackService.php',
            'security.authentication.provider.simple_preauth.shopify_webhook' => 'getSecurity_Authentication_Provider_SimplePreauth_ShopifyWebhookService.php',
            'security.authentication.session_strategy.api' => 'getSecurity_Authentication_SessionStrategy_ApiService.php',
            'security.authentication.session_strategy.shopify_webhook' => 'getSecurity_Authentication_SessionStrategy_ShopifyWebhookService.php',
            'security.authentication.switchuser_listener.main' => 'getSecurity_Authentication_SwitchuserListener_MainService.php',
            'security.authentication.trust_resolver' => 'getSecurity_Authentication_TrustResolverService.php',
            'security.authentication_utils' => 'getSecurity_AuthenticationUtilsService.php',
            'security.channel_listener' => 'getSecurity_ChannelListenerService.php',
            'security.command.user_password_encoder' => 'getSecurity_Command_UserPasswordEncoderService.php',
            'security.context_listener.0' => 'getSecurity_ContextListener_0Service.php',
            'security.context_listener.1' => 'getSecurity_ContextListener_1Service.php',
            'security.csrf.token_manager' => 'getSecurity_Csrf_TokenManagerService.php',
            'security.csrf.token_storage' => 'getSecurity_Csrf_TokenStorageService.php',
            'security.encoder_factory' => 'getSecurity_EncoderFactoryService.php',
            'security.firewall.map.context.api' => 'getSecurity_Firewall_Map_Context_ApiService.php',
            'security.firewall.map.context.magento2_oauth_callback' => 'getSecurity_Firewall_Map_Context_Magento2OauthCallbackService.php',
            'security.firewall.map.context.main' => 'getSecurity_Firewall_Map_Context_MainService.php',
            'security.firewall.map.context.shopify_webhook' => 'getSecurity_Firewall_Map_Context_ShopifyWebhookService.php',
            'security.http_utils' => 'getSecurity_HttpUtilsService.php',
            'security.password_encoder' => 'getSecurity_PasswordEncoderService.php',
            'security.request_matcher.00qf1z7' => 'getSecurity_RequestMatcher_00qf1z7Service.php',
            'security.request_matcher.kxgqwfa' => 'getSecurity_RequestMatcher_KxgqwfaService.php',
            'security.request_matcher.umgy0tl' => 'getSecurity_RequestMatcher_Umgy0tlService.php',
            'security.request_matcher.x1icpav' => 'getSecurity_RequestMatcher_X1icpavService.php',
            'security.role_hierarchy' => 'getSecurity_RoleHierarchyService.php',
            'security.user_checker' => 'getSecurity_UserCheckerService.php',
            'security.user_value_resolver' => 'getSecurity_UserValueResolverService.php',
            'security.validator.user_password' => 'getSecurity_Validator_UserPasswordService.php',
            'sensio_framework_extra.view.guesser' => 'getSensioFrameworkExtra_View_GuesserService.php',
            'serializer' => 'getSerializerService.php',
            'serializer.mapping.cache.symfony' => 'getSerializer_Mapping_Cache_SymfonyService.php',
            'serializer.mapping.cache_warmer' => 'getSerializer_Mapping_CacheWarmerService.php',
            'service_locator.1onhtm9' => 'getServiceLocator_1onhtm9Service.php',
            'service_locator.1ydjmz8' => 'getServiceLocator_1ydjmz8Service.php',
            'service_locator.5h4cvxx' => 'getServiceLocator_5h4cvxxService.php',
            'service_locator.8_1bzul' => 'getServiceLocator_81bzulService.php',
            'service_locator.9.gp1se' => 'getServiceLocator_9_Gp1seService.php',
            'service_locator._ayg34g' => 'getServiceLocator_Ayg34gService.php',
            'service_locator.antabtk' => 'getServiceLocator_AntabtkService.php',
            'service_locator.ba7jlor' => 'getServiceLocator_Ba7jlorService.php',
            'service_locator.eyatk19' => 'getServiceLocator_Eyatk19Service.php',
            'service_locator.f0jopj2' => 'getServiceLocator_F0jopj2Service.php',
            'service_locator.gr7jsx0' => 'getServiceLocator_Gr7jsx0Service.php',
            'service_locator.gtpaxub' => 'getServiceLocator_GtpaxubService.php',
            'service_locator.hmznkve' => 'getServiceLocator_HmznkveService.php',
            'service_locator.jwvgbsz' => 'getServiceLocator_JwvgbszService.php',
            'service_locator.k4wsrws' => 'getServiceLocator_K4wsrwsService.php',
            'service_locator.lr3dryr' => 'getServiceLocator_Lr3dryrService.php',
            'service_locator.mh805et' => 'getServiceLocator_Mh805etService.php',
            'service_locator.q.pbxzg' => 'getServiceLocator_Q_PbxzgService.php',
            'service_locator.qmqcrta' => 'getServiceLocator_QmqcrtaService.php',
            'service_locator.r9yn3ey' => 'getServiceLocator_R9yn3eyService.php',
            'service_locator.rvc0r7z' => 'getServiceLocator_Rvc0r7zService.php',
            'service_locator.tfkjvqo' => 'getServiceLocator_TfkjvqoService.php',
            'service_locator.tkotenx' => 'getServiceLocator_TkotenxService.php',
            'service_locator.y8eaw7b' => 'getServiceLocator_Y8eaw7bService.php',
            'service_locator.yscbjmj' => 'getServiceLocator_YscbjmjService.php',
            'services_resetter' => 'getServicesResetterService.php',
            'session' => 'getSessionService.php',
            'session.handler' => 'getSession_HandlerService.php',
            'session.storage.filesystem' => 'getSession_Storage_FilesystemService.php',
            'session.storage.metadata_bag' => 'getSession_Storage_MetadataBagService.php',
            'session.storage.native' => 'getSession_Storage_NativeService.php',
            'session.storage.php_bridge' => 'getSession_Storage_PhpBridgeService.php',
            'swiftmailer.command.debug' => 'getSwiftmailer_Command_DebugService.php',
            'swiftmailer.command.new_email' => 'getSwiftmailer_Command_NewEmailService.php',
            'swiftmailer.command.send_email' => 'getSwiftmailer_Command_SendEmailService.php',
            'swiftmailer.email_sender.listener' => 'getSwiftmailer_EmailSender_ListenerService.php',
            'swiftmailer.mailer.default' => 'getSwiftmailer_Mailer_DefaultService.php',
            'swiftmailer.mailer.default.plugin.messagelogger' => 'getSwiftmailer_Mailer_Default_Plugin_MessageloggerService.php',
            'tactician.command.debug' => 'getTactician_Command_DebugService.php',
            'tactician.commandbus.default' => 'getTactician_Commandbus_DefaultService.php',
            'tactician.commandbus.default.handler.locator' => 'getTactician_Commandbus_Default_Handler_LocatorService.php',
            'tactician.commandbus.default.middleware.command_handler' => 'getTactician_Commandbus_Default_Middleware_CommandHandlerService.php',
            'tactician.handler.command_name_extractor.class_name' => 'getTactician_Handler_CommandNameExtractor_ClassNameService.php',
            'tactician.handler.method_name_inflector.class_name' => 'getTactician_Handler_MethodNameInflector_ClassNameService.php',
            'tactician.handler.method_name_inflector.handle' => 'getTactician_Handler_MethodNameInflector_HandleService.php',
            'tactician.handler.method_name_inflector.handle_class_name' => 'getTactician_Handler_MethodNameInflector_HandleClassNameService.php',
            'tactician.handler.method_name_inflector.handle_class_name_without_suffix' => 'getTactician_Handler_MethodNameInflector_HandleClassNameWithoutSuffixService.php',
            'tactician.handler.method_name_inflector.invoke' => 'getTactician_Handler_MethodNameInflector_InvokeService.php',
            'tactician.middleware.doctrine.default' => 'getTactician_Middleware_Doctrine_DefaultService.php',
            'tactician.middleware.doctrine_rollback_only.default' => 'getTactician_Middleware_DoctrineRollbackOnly_DefaultService.php',
            'tactician.middleware.locking' => 'getTactician_Middleware_LockingService.php',
            'tactician.middleware.security' => 'getTactician_Middleware_SecurityService.php',
            'tactician.middleware.validator' => 'getTactician_Middleware_ValidatorService.php',
            'tactician.plugins.named_command.extractor' => 'getTactician_Plugins_NamedCommand_ExtractorService.php',
            'templating' => 'getTemplatingService.php',
            'templating.cache_warmer.template_paths' => 'getTemplating_CacheWarmer_TemplatePathsService.php',
            'templating.filename_parser' => 'getTemplating_FilenameParserService.php',
            'templating.finder' => 'getTemplating_FinderService.php',
            'templating.helper.logout_url' => 'getTemplating_Helper_LogoutUrlService.php',
            'templating.helper.security' => 'getTemplating_Helper_SecurityService.php',
            'templating.loader' => 'getTemplating_LoaderService.php',
            'translation.dumper.csv' => 'getTranslation_Dumper_CsvService.php',
            'translation.dumper.ini' => 'getTranslation_Dumper_IniService.php',
            'translation.dumper.json' => 'getTranslation_Dumper_JsonService.php',
            'translation.dumper.mo' => 'getTranslation_Dumper_MoService.php',
            'translation.dumper.php' => 'getTranslation_Dumper_PhpService.php',
            'translation.dumper.po' => 'getTranslation_Dumper_PoService.php',
            'translation.dumper.qt' => 'getTranslation_Dumper_QtService.php',
            'translation.dumper.res' => 'getTranslation_Dumper_ResService.php',
            'translation.dumper.xliff' => 'getTranslation_Dumper_XliffService.php',
            'translation.dumper.yml' => 'getTranslation_Dumper_YmlService.php',
            'translation.extractor' => 'getTranslation_ExtractorService.php',
            'translation.extractor.php' => 'getTranslation_Extractor_PhpService.php',
            'translation.loader' => 'getTranslation_LoaderService.php',
            'translation.loader.csv' => 'getTranslation_Loader_CsvService.php',
            'translation.loader.dat' => 'getTranslation_Loader_DatService.php',
            'translation.loader.ini' => 'getTranslation_Loader_IniService.php',
            'translation.loader.json' => 'getTranslation_Loader_JsonService.php',
            'translation.loader.mo' => 'getTranslation_Loader_MoService.php',
            'translation.loader.php' => 'getTranslation_Loader_PhpService.php',
            'translation.loader.po' => 'getTranslation_Loader_PoService.php',
            'translation.loader.qt' => 'getTranslation_Loader_QtService.php',
            'translation.loader.res' => 'getTranslation_Loader_ResService.php',
            'translation.loader.xliff' => 'getTranslation_Loader_XliffService.php',
            'translation.loader.yml' => 'getTranslation_Loader_YmlService.php',
            'translation.reader' => 'getTranslation_ReaderService.php',
            'translation.warmer' => 'getTranslation_WarmerService.php',
            'translation.writer' => 'getTranslation_WriterService.php',
            'twig.cache_warmer' => 'getTwig_CacheWarmerService.php',
            'twig.command.debug' => 'getTwig_Command_DebugService.php',
            'twig.command.lint' => 'getTwig_Command_LintService.php',
            'twig.controller.exception' => 'getTwig_Controller_ExceptionService.php',
            'twig.controller.preview_error' => 'getTwig_Controller_PreviewErrorService.php',
            'twig.exception_listener' => 'getTwig_ExceptionListenerService.php',
            'twig.form.renderer' => 'getTwig_Form_RendererService.php',
            'twig.runtime.httpkernel' => 'getTwig_Runtime_HttpkernelService.php',
            'twig.template_cache_warmer' => 'getTwig_TemplateCacheWarmerService.php',
            'twig.translation.extractor' => 'getTwig_Translation_ExtractorService.php',
            'uri_signer' => 'getUriSignerService.php',
            'validator.email' => 'getValidator_EmailService.php',
            'validator.expression' => 'getValidator_ExpressionService.php',
            'validator.mapping.cache_warmer' => 'getValidator_Mapping_CacheWarmerService.php',
            'web_profiler.controller.exception' => 'getWebProfiler_Controller_ExceptionService.php',
            'web_profiler.controller.profiler' => 'getWebProfiler_Controller_ProfilerService.php',
            'web_profiler.controller.router' => 'getWebProfiler_Controller_RouterService.php',
            'webpack_encore.entrypoint_lookup.cache_warmer' => 'getWebpackEncore_EntrypointLookup_CacheWarmerService.php',
            'webpack_encore.entrypoint_lookup[_default]' => 'getWebpackEncore_EntrypointLookupDefaultService.php',
            'webpack_encore.entrypoint_lookup_collection' => 'getWebpackEncore_EntrypointLookupCollectionService.php',
            'webpack_encore.exception_listener' => 'getWebpackEncore_ExceptionListenerService.php',
            'webpack_encore.tag_renderer' => 'getWebpackEncore_TagRendererService.php',
        ];
        $this->privates = [
            'FOS\\RestBundle\\Request\\ParamFetcherInterface' => true,
            'League\\Tactician\\CommandBus' => true,
            'Symfony\\WebpackEncoreBundle\\Asset\\EntrypointLookupInterface' => true,
            'fos_rest.router' => true,
            'fos_rest.templating' => true,
            'gumstix_rest.access_denied_handler' => true,
            'gumstix_rest.form_error_normalizer' => true,
            'gumstix_sso.cookie_auth' => true,
            'gumstix_sso.credential_storage' => true,
            'gumstix_sso.credential_storage_file' => true,
            'gumstix_sso.header_auth' => true,
            'gumstix_sso.http_client_factory' => true,
            'gumstix_sso.login_auth' => true,
            'gumstix_sso.logout_service' => true,
            'gumstix_sso.sso' => true,
            'gumstix_sso.sso_factory' => true,
            'gumstix_sso.twig_extension' => true,
            'security.authentication.session_strategy.magento2_oauth_callback' => true,
            'security.authentication.session_strategy.main' => true,
            'session.storage' => true,
            'swiftmailer.mailer' => true,
            'swiftmailer.mailer.transport.fake_transport' => true,
            'swiftmailer.plugin.messagelogger' => true,
            'tactician.commandbus' => true,
            'tactician.handler.locator.symfony' => true,
            'tactician.middleware.command_handler' => true,
            'tactician.middleware.doctrine' => true,
            'tactician.middleware.doctrine_rollback_only' => true,
            'FOS\\RestBundle\\View\\ViewHandlerInterface' => true,
            'Fabiang\\Xmpp\\Client' => true,
            'Gumstix\\GeographyBundle\\Twig\\GeographyExtension' => true,
            'Gumstix\\RestBundle\\Handler\\AccessDeniedHandler' => true,
            'Gumstix\\RestBundle\\Serializer\\FormErrorNormalizer' => true,
            'Gumstix\\SSOBundle\\Security\\CookieAuthenticator' => true,
            'Gumstix\\SSOBundle\\Security\\HeaderAuthenticator' => true,
            'Gumstix\\SSOBundle\\Security\\LoginAuthenticator' => true,
            'Gumstix\\SSOBundle\\Service\\HttpClientFactory' => true,
            'Gumstix\\SSOBundle\\Service\\LogoutService' => true,
            'Gumstix\\SSOBundle\\Service\\SingleSignOnFactory' => true,
            'Gumstix\\SSOBundle\\Twig\\SSOExtension' => true,
            'Gumstix\\SSO\\Service\\CredentialStorage' => true,
            'Gumstix\\SSO\\Service\\Router' => true,
            'Rialto\\Allocation\\Allocation\\AllocationTransferListener' => true,
            'Rialto\\Allocation\\Allocation\\EmptyAllocationRemover' => true,
            'Rialto\\Allocation\\Consumer\\StockConsumerListener' => true,
            'Rialto\\Allocation\\Dispatch\\DispatchInstructionSubscriber' => true,
            'Rialto\\Allocation\\EstimatedArrivalDate\\EstimatedArrivalDateGenerator' => true,
            'Rialto\\Allocation\\Validator\\PurchasingDataExistsForChildValidator' => true,
            'Rialto\\Allocation\\Validator\\PurchasingDataExistsValidator' => true,
            'Rialto\\Catalina\\ProductionTaskListener' => true,
            'Rialto\\Cms\\CmsLoader' => true,
            'Rialto\\Cms\\ExceptionHandler' => true,
            'Rialto\\Cms\\Web\\CmsEntryType' => true,
            'Rialto\\Database\\Orm\\LockExceptionHandler' => true,
            'Rialto\\Filesystem\\TempFilesystem' => true,
            'Rialto\\Geography\\Address\\Web\\AddressEntityType' => true,
            'Rialto\\Geppetto\\StandardCostListener' => true,
            'Rialto\\Legacy\\CurlHelper' => true,
            'Rialto\\Madison\\Feature\\Web\\FeatureType' => true,
            'Rialto\\Madison\\MadisonClient' => true,
            'Rialto\\Madison\\Version\\VersionChangeCache' => true,
            'Rialto\\Magento2\\Firewall\\MagentoAuthenticator' => true,
            'Rialto\\Magento2\\Firewall\\StorefrontUserProvider' => true,
            'Rialto\\Magento2\\Order\\OrderClosedListener' => true,
            'Rialto\\Magento2\\Order\\PaymentProcessor' => true,
            'Rialto\\Magento2\\Order\\ShipmentListener' => true,
            'Rialto\\Magento2\\Order\\SuspectedFraudListener' => true,
            'Rialto\\Magento2\\Stock\\StockUpdateListener' => true,
            'Rialto\\Manufacturing\\Allocation\\Command\\AllocateHandler' => true,
            'Rialto\\Manufacturing\\Bom\\Validator\\IsValidBomCsvValidator' => true,
            'Rialto\\Manufacturing\\BuildFiles\\PcbBuildFileVoter' => true,
            'Rialto\\Manufacturing\\Customization\\Customizer' => true,
            'Rialto\\Manufacturing\\Customization\\Web\\CustomizationStrategyType' => true,
            'Rialto\\Manufacturing\\Kit\\Reminder\\EmailScheduler' => true,
            'Rialto\\Manufacturing\\PurchaseOrder\\Command\\OrderPartsHandler' => true,
            'Rialto\\Manufacturing\\PurchaseOrder\\Command\\UserSelectManufacturerToOrderHandler' => true,
            'Rialto\\Manufacturing\\PurchaseOrder\\PartsOrderSentListener' => true,
            'Rialto\\Manufacturing\\Task\\ProductionTaskRefreshListener' => true,
            'Rialto\\Manufacturing\\WorkOrder\\TransferEventListener' => true,
            'Rialto\\Panelization\\Validator\\PurchasingDataExistsValidator' => true,
            'Rialto\\Payment\\FakeGateway' => true,
            'Rialto\\Payment\\PaymentProcessor' => true,
            'Rialto\\PcbNg\\Command\\CreateManufacturedStockItemPcbNgPurchasingDataHandler' => true,
            'Rialto\\PcbNg\\Command\\ProcessPcbNgEmailsHandler' => true,
            'Rialto\\PcbNg\\Service\\GerbersConverter' => true,
            'Rialto\\PcbNg\\Service\\LocationsConverter' => true,
            'Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer' => true,
            'Rialto\\PcbNg\\Service\\PcbNgSubmitter' => true,
            'Rialto\\PcbNg\\Service\\PickAndPlaceFactory' => true,
            'Rialto\\Port\\CommandBus\\CommandQueue' => true,
            'Rialto\\Port\\FormatConversion\\PostScriptToPdfConverter' => true,
            'Rialto\\Purchasing\\Catalog\\Command\\RefreshPurchasingDataStockLevelHandler' => true,
            'Rialto\\Purchasing\\Catalog\\Orm\\PurchasingDataRepository' => true,
            'Rialto\\Purchasing\\Catalog\\PurchasingDataSynchronizer' => true,
            'Rialto\\Purchasing\\EmailEventSubscriber' => true,
            'Rialto\\Purchasing\\Invoice\\Reader\\Email\\AttachmentLocator' => true,
            'Rialto\\Purchasing\\Invoice\\Reader\\Email\\SupplierMailbox' => true,
            'Rialto\\Purchasing\\Invoice\\Web\\SupplierInvoiceItemApprovalType' => true,
            'Rialto\\Purchasing\\Manufacturer\\Cli\\BulkPushModuleManufacturersConsoleCommand' => true,
            'Rialto\\Purchasing\\Order\\Attachment\\PurchaseOrderAttachmentGenerator' => true,
            'Rialto\\Purchasing\\Order\\Command\\MergePurchaseOrdersHandler' => true,
            'Rialto\\Purchasing\\Order\\PurchaseOrderVoter' => true,
            'Rialto\\Purchasing\\Order\\StockItemVoter' => true,
            'Rialto\\Purchasing\\Order\\Web\\CreatePurchaseOrderType' => true,
            'Rialto\\Purchasing\\Order\\Web\\EditPurchaseOrderType' => true,
            'Rialto\\Purchasing\\Producer\\StockProducerVoter' => true,
            'Rialto\\Purchasing\\Producer\\Web\\StockProducerType' => true,
            'Rialto\\Purchasing\\PurchasingErrorHandler' => true,
            'Rialto\\Purchasing\\Receiving\\Auth\\CanReceiveIntoValidator' => true,
            'Rialto\\Purchasing\\Receiving\\Auth\\ReceiveIntoVoter' => true,
            'Rialto\\Purchasing\\Receiving\\GoodsReceivedLogger' => true,
            'Rialto\\Purchasing\\Receiving\\Notify\\XmppEventSubscriber' => true,
            'Rialto\\Purchasing\\Receiving\\Web\\GoodsReceivedType' => true,
            'Rialto\\Purchasing\\Supplier\\Attribute\\Web\\SupplierAttributeType' => true,
            'Rialto\\Sales\\Order\\Dates\\TargetShipDateListener' => true,
            'Rialto\\Sales\\Order\\Email\\OrderEmailListener' => true,
            'Rialto\\Sales\\Order\\OrderUpdateListener' => true,
            'Rialto\\Sales\\Order\\SoftwareInvoicer' => true,
            'Rialto\\Sales\\Shipping\\ApproveToShipEventListener' => true,
            'Rialto\\Security\\Firewall\\ByUsernameProvider' => true,
            'Rialto\\Security\\Firewall\\ByUuidProvider' => true,
            'Rialto\\Security\\Firewall\\UsernameNotFoundExceptionHandler' => true,
            'Rialto\\Security\\Nda\\NdaFormListener' => true,
            'Rialto\\Security\\User\\LastLoginUpdater' => true,
            'Rialto\\Security\\User\\UserManager' => true,
            'Rialto\\Security\\User\\UserVoter' => true,
            'Rialto\\Security\\User\\Web\\UserType' => true,
            'Rialto\\Shipping\\Export\\AllowedCountryValidator' => true,
            'Rialto\\Shipping\\Shipment\\Web\\ShipmentOptionsType' => true,
            'Rialto\\Shopify\\Order\\FulfillmentListener' => true,
            'Rialto\\Shopify\\Order\\OrderClosedListener' => true,
            'Rialto\\Shopify\\Order\\PaymentProcessor' => true,
            'Rialto\\Shopify\\Webhook\\ShopifyUserProvider' => true,
            'Rialto\\Shopify\\Webhook\\WebhookAuthenticator' => true,
            'Rialto\\Stock\\Bin\\StockBinVoter' => true,
            'Rialto\\Stock\\Bin\\Web\\BinUpdateAllocType' => true,
            'Rialto\\Stock\\Bin\\Web\\StockAdjustmentType' => true,
            'Rialto\\Stock\\Count\\StockCountVoter' => true,
            'Rialto\\Stock\\Item\\Command\\RefreshStockLevelHandler' => true,
            'Rialto\\Stock\\Item\\NewSkuValidator' => true,
            'Rialto\\Stock\\Item\\StockItemDeleteService' => true,
            'Rialto\\Stock\\Item\\Version\\Web\\ItemVersionSelectorType' => true,
            'Rialto\\Stock\\Item\\Web\\EditType' => true,
            'Rialto\\Stock\\Item\\Web\\StockItemAttributeType' => true,
            'Rialto\\Stock\\Item\\Web\\StockItemTemplateType' => true,
            'Rialto\\Stock\\Shelf\\Position\\AssignmentListener' => true,
            'Rialto\\Stock\\Transfer\\BinEventListener' => true,
            'Rialto\\Summary\\Menu\\SummaryVoter' => true,
            'Rialto\\Supplier\\Logger' => true,
            'Rialto\\Supplier\\Order\\Email\\EmailSubscriber' => true,
            'Rialto\\Supplier\\SupplierVoter' => true,
            'Rialto\\Task\\TaskVoter' => true,
            'Rialto\\Ups\\Shipping\\Label\\ShippingLabelListener' => true,
            'Rialto\\Ups\\Shipping\\Webservice\\UpsApiService' => true,
            'Rialto\\Ups\\TrackingRecord\\Command\\UpdateTrackingRecordHandler' => true,
            'Rialto\\Web\\Form\\JsEntityType' => true,
            'Rialto\\Web\\Form\\NumberTypeExtension' => true,
            'Rialto\\Web\\Form\\TextEntityType' => true,
            'Rialto\\Wordpress\\ChangeNoticeListener' => true,
            'annotation_reader' => true,
            'annotations.cache' => true,
            'annotations.cache_warmer' => true,
            'annotations.reader' => true,
            'argument_resolver.default' => true,
            'argument_resolver.request' => true,
            'argument_resolver.request_attribute' => true,
            'argument_resolver.service' => true,
            'argument_resolver.session' => true,
            'argument_resolver.variadic' => true,
            'assets._version__default' => true,
            'assets.context' => true,
            'assets.packages' => true,
            'autowired.Rialto\\Accounting\\Bank\\Transfer\\BankTransfer' => true,
            'autowired.Rialto\\Purchasing\\Invoice\\SupplierInvoice' => true,
            'autowired.Rialto\\Purchasing\\Invoice\\SupplierInvoiceItem' => true,
            'autowired.Rialto\\Purchasing\\Supplier\\Supplier' => true,
            'autowired.Rialto\\Stock\\Item\\ManufacturedStockItem' => true,
            'autowired.Rialto\\Stock\\Item\\PurchasedStockItem' => true,
            'cache.annotations' => true,
            'cache.default_clearer' => true,
            'cache.serializer' => true,
            'cache.validator' => true,
            'cache.webpack_encore' => true,
            'config.resource.self_checking_resource_checker' => true,
            'config_cache_factory' => true,
            'console.command.about' => true,
            'console.command.assets_install' => true,
            'console.command.cache_clear' => true,
            'console.command.cache_pool_clear' => true,
            'console.command.cache_pool_prune' => true,
            'console.command.cache_warmup' => true,
            'console.command.config_debug' => true,
            'console.command.config_dump_reference' => true,
            'console.command.container_debug' => true,
            'console.command.debug_autowiring' => true,
            'console.command.event_dispatcher_debug' => true,
            'console.command.form_debug' => true,
            'console.command.router_debug' => true,
            'console.command.router_match' => true,
            'console.command.translation_debug' => true,
            'console.command.translation_update' => true,
            'console.command.xliff_lint' => true,
            'console.command.yaml_lint' => true,
            'console.error_listener' => true,
            'controller_name_converter' => true,
            'craue.form.flow' => true,
            'craue.form.flow.data_manager' => true,
            'craue.form.flow.event_listener.flow_expired' => true,
            'craue.form.flow.event_listener.previous_step_invalid' => true,
            'craue.form.flow.form_extension' => true,
            'craue.form.flow.hidden_field_extension' => true,
            'debug.argument_resolver' => true,
            'debug.controller_resolver' => true,
            'debug.debug_handlers_listener' => true,
            'debug.event_dispatcher' => true,
            'debug.file_link_formatter' => true,
            'debug.file_link_formatter.url_format' => true,
            'debug.security.access.decision_manager' => true,
            'debug.stopwatch' => true,
            'dependency_injection.config.container_parameters_resource_checker' => true,
            'deprecated.form.registry' => true,
            'deprecated.form.registry.csrf' => true,
            'doctrine.cache_clear_metadata_command' => true,
            'doctrine.cache_clear_query_cache_command' => true,
            'doctrine.cache_clear_result_command' => true,
            'doctrine.cache_collection_region_command' => true,
            'doctrine.clear_entity_region_command' => true,
            'doctrine.clear_query_region_command' => true,
            'doctrine.database_create_command' => true,
            'doctrine.database_drop_command' => true,
            'doctrine.database_import_command' => true,
            'doctrine.dbal.connection_factory' => true,
            'doctrine.ensure_production_settings_command' => true,
            'doctrine.generate_entities_command' => true,
            'doctrine.mapping_convert_command' => true,
            'doctrine.mapping_import_command' => true,
            'doctrine.mapping_info_command' => true,
            'doctrine.orm.default_entity_listener_resolver' => true,
            'doctrine.orm.default_entity_manager.property_info_extractor' => true,
            'doctrine.orm.default_listeners.attach_entity_listeners' => true,
            'doctrine.orm.default_manager_configurator' => true,
            'doctrine.orm.proxy_cache_warmer' => true,
            'doctrine.orm.validator.unique' => true,
            'doctrine.orm.validator_initializer' => true,
            'doctrine.query_dql_command' => true,
            'doctrine.query_sql_command' => true,
            'doctrine.schema_create_command' => true,
            'doctrine.schema_drop_command' => true,
            'doctrine.schema_update_command' => true,
            'doctrine.schema_validate_command' => true,
            'doctrine_cache.contains_command' => true,
            'doctrine_cache.delete_command' => true,
            'doctrine_cache.flush_command' => true,
            'doctrine_cache.stats_command' => true,
            'doctrine_migrations.diff_command' => true,
            'doctrine_migrations.execute_command' => true,
            'doctrine_migrations.generate_command' => true,
            'doctrine_migrations.latest_command' => true,
            'doctrine_migrations.migrate_command' => true,
            'doctrine_migrations.status_command' => true,
            'doctrine_migrations.version_command' => true,
            'easyadmin.form.type' => true,
            'easyadmin.form.type.autocomplete' => true,
            'easyadmin.form.type.divider' => true,
            'easyadmin.form.type.extension' => true,
            'easyadmin.form.type.group' => true,
            'easyadmin.form.type.section' => true,
            'file_locator' => true,
            'form.registry' => true,
            'form.resolved_type_factory' => true,
            'form.server_params' => true,
            'form.type.choice' => true,
            'form.type.entity' => true,
            'form.type.form' => true,
            'form.type_extension.csrf' => true,
            'form.type_extension.form.http_foundation' => true,
            'form.type_extension.form.transformation_failure_handling' => true,
            'form.type_extension.form.validator' => true,
            'form.type_extension.repeated.validator' => true,
            'form.type_extension.submit.validator' => true,
            'form.type_extension.upload.validator' => true,
            'form.type_guesser.doctrine' => true,
            'form.type_guesser.validator' => true,
            'fos_js_routing.dump_command' => true,
            'fos_js_routing.router_debug_exposed_command' => true,
            'fos_rest.body_listener' => true,
            'fos_rest.decoder.json' => true,
            'fos_rest.decoder.jsontoform' => true,
            'fos_rest.decoder.xml' => true,
            'fos_rest.decoder_provider' => true,
            'fos_rest.exception.codes_map' => true,
            'fos_rest.exception.messages_map' => true,
            'fos_rest.exception_listener' => true,
            'fos_rest.format_listener' => true,
            'fos_rest.format_negotiator' => true,
            'fos_rest.inflector' => true,
            'fos_rest.normalizer.camel_keys' => true,
            'fos_rest.normalizer.camel_keys_with_leading_underscore' => true,
            'fos_rest.request.param_fetcher' => true,
            'fos_rest.request.param_fetcher.reader' => true,
            'fos_rest.serializer' => true,
            'fos_rest.serializer.exception_normalizer.jms' => true,
            'fos_rest.serializer.form_error_handler' => true,
            'fos_rest.serializer.jms_handler_registry.inner' => true,
            'fos_rest.view_response_listener' => true,
            'fragment.handler' => true,
            'fragment.renderer.inline' => true,
            'gumstix_form.twig_extension' => true,
            'gumstix_sso.credential_storage_database' => true,
            'gumstix_sso.router' => true,
            'jms_job_queue.command.clean_up' => true,
            'jms_job_queue.command.mark_job_incomplete' => true,
            'jms_job_queue.command.run' => true,
            'jms_job_queue.command.schedule' => true,
            'jms_job_queue.retry_scheduler' => true,
            'jms_job_queue.twig.extension' => true,
            'jms_serializer.accessor_strategy' => true,
            'jms_serializer.expression_evaluator' => true,
            'jms_serializer.handler_registry' => true,
            'jms_serializer.serialized_name_annotation_strategy' => true,
            'jms_serializer.templating.helper.serializer' => true,
            'jms_serializer.unserialize_object_constructor' => true,
            'kernel.class_cache.cache_warmer' => true,
            'locale_listener' => true,
            'logger' => true,
            'monolog.handler.automation' => true,
            'monolog.handler.console' => true,
            'monolog.handler.doctrine' => true,
            'monolog.handler.email' => true,
            'monolog.handler.flash' => true,
            'monolog.handler.null_internal' => true,
            'monolog.handler.php' => true,
            'monolog.handler.production' => true,
            'monolog.handler.sentry' => true,
            'monolog.handler.ups' => true,
            'monolog.logger.automation' => true,
            'monolog.logger.cache' => true,
            'monolog.logger.command_bus' => true,
            'monolog.logger.console' => true,
            'monolog.logger.doctrine' => true,
            'monolog.logger.email' => true,
            'monolog.logger.event' => true,
            'monolog.logger.flash' => true,
            'monolog.logger.manufacturing' => true,
            'monolog.logger.php' => true,
            'monolog.logger.receiving' => true,
            'monolog.logger.request' => true,
            'monolog.logger.sales' => true,
            'monolog.logger.security' => true,
            'monolog.logger.supplier' => true,
            'monolog.logger.templating' => true,
            'monolog.logger.translation' => true,
            'monolog.logger.ups' => true,
            'monolog.processor.psr_log_message' => true,
            'nelmio_cors.cors_listener' => true,
            'nelmio_cors.options_provider.config' => true,
            'nelmio_security.clickjacking_listener' => true,
            'nelmio_security.external_redirect.target_validator' => true,
            'nelmio_security.external_redirect_listener' => true,
            'property_accessor' => true,
            'resolve_controller_name_subscriber' => true,
            'response_listener' => true,
            'router.cache_warmer' => true,
            'router.request_context' => true,
            'router_listener' => true,
            'security.access.authenticated_voter' => true,
            'security.access.expression_voter' => true,
            'security.access.role_hierarchy_voter' => true,
            'security.access_listener' => true,
            'security.access_map' => true,
            'security.authentication.guard_handler' => true,
            'security.authentication.listener.guard.api' => true,
            'security.authentication.listener.guard.main' => true,
            'security.authentication.listener.simple_preauth.magento2_oauth_callback' => true,
            'security.authentication.listener.simple_preauth.shopify_webhook' => true,
            'security.authentication.manager' => true,
            'security.authentication.provider.guard.api' => true,
            'security.authentication.provider.guard.main' => true,
            'security.authentication.provider.simple_preauth.magento2_oauth_callback' => true,
            'security.authentication.provider.simple_preauth.shopify_webhook' => true,
            'security.authentication.session_strategy.api' => true,
            'security.authentication.session_strategy.shopify_webhook' => true,
            'security.authentication.switchuser_listener.main' => true,
            'security.authentication.trust_resolver' => true,
            'security.channel_listener' => true,
            'security.command.user_password_encoder' => true,
            'security.context_listener.0' => true,
            'security.context_listener.1' => true,
            'security.csrf.token_storage' => true,
            'security.encoder_factory' => true,
            'security.firewall' => true,
            'security.firewall.map.context.api' => true,
            'security.firewall.map.context.magento2_oauth_callback' => true,
            'security.firewall.map.context.main' => true,
            'security.firewall.map.context.shopify_webhook' => true,
            'security.http_utils' => true,
            'security.logout_url_generator' => true,
            'security.rememberme.response_listener' => true,
            'security.request_matcher.00qf1z7' => true,
            'security.request_matcher.kxgqwfa' => true,
            'security.request_matcher.umgy0tl' => true,
            'security.request_matcher.x1icpav' => true,
            'security.role_hierarchy' => true,
            'security.user_checker' => true,
            'security.user_value_resolver' => true,
            'security.validator.user_password' => true,
            'sensio_framework_extra.controller.listener' => true,
            'sensio_framework_extra.converter.datetime' => true,
            'sensio_framework_extra.converter.doctrine.orm' => true,
            'sensio_framework_extra.converter.listener' => true,
            'sensio_framework_extra.converter.manager' => true,
            'sensio_framework_extra.view.listener' => true,
            'serializer.mapping.cache.symfony' => true,
            'serializer.mapping.cache_warmer' => true,
            'service_locator.1onhtm9' => true,
            'service_locator.1ydjmz8' => true,
            'service_locator.5h4cvxx' => true,
            'service_locator.8_1bzul' => true,
            'service_locator.9.gp1se' => true,
            'service_locator._ayg34g' => true,
            'service_locator.antabtk' => true,
            'service_locator.ba7jlor' => true,
            'service_locator.eyatk19' => true,
            'service_locator.f0jopj2' => true,
            'service_locator.gr7jsx0' => true,
            'service_locator.gtpaxub' => true,
            'service_locator.hmznkve' => true,
            'service_locator.jwvgbsz' => true,
            'service_locator.k4wsrws' => true,
            'service_locator.lr3dryr' => true,
            'service_locator.mh805et' => true,
            'service_locator.q.pbxzg' => true,
            'service_locator.qmqcrta' => true,
            'service_locator.r9yn3ey' => true,
            'service_locator.rvc0r7z' => true,
            'service_locator.tfkjvqo' => true,
            'service_locator.tkotenx' => true,
            'service_locator.y8eaw7b' => true,
            'service_locator.yscbjmj' => true,
            'session.handler' => true,
            'session.save_listener' => true,
            'session.storage.filesystem' => true,
            'session.storage.metadata_bag' => true,
            'session.storage.native' => true,
            'session.storage.php_bridge' => true,
            'session_listener' => true,
            'streamed_response_listener' => true,
            'swiftmailer.command.debug' => true,
            'swiftmailer.command.new_email' => true,
            'swiftmailer.command.send_email' => true,
            'swiftmailer.email_sender.listener' => true,
            'tactician.command.debug' => true,
            'tactician.commandbus.default.handler.locator' => true,
            'tactician.commandbus.default.middleware.command_handler' => true,
            'tactician.handler.command_name_extractor.class_name' => true,
            'tactician.handler.method_name_inflector.class_name' => true,
            'tactician.handler.method_name_inflector.handle' => true,
            'tactician.handler.method_name_inflector.handle_class_name' => true,
            'tactician.handler.method_name_inflector.handle_class_name_without_suffix' => true,
            'tactician.handler.method_name_inflector.invoke' => true,
            'tactician.middleware.doctrine.default' => true,
            'tactician.middleware.doctrine_rollback_only.default' => true,
            'tactician.middleware.locking' => true,
            'tactician.middleware.security' => true,
            'tactician.middleware.validator' => true,
            'tactician.plugins.named_command.extractor' => true,
            'templating.cache_warmer.template_paths' => true,
            'templating.filename_parser' => true,
            'templating.finder' => true,
            'templating.helper.logout_url' => true,
            'templating.helper.security' => true,
            'templating.locator' => true,
            'templating.name_parser' => true,
            'translation.dumper.csv' => true,
            'translation.dumper.ini' => true,
            'translation.dumper.json' => true,
            'translation.dumper.mo' => true,
            'translation.dumper.php' => true,
            'translation.dumper.po' => true,
            'translation.dumper.qt' => true,
            'translation.dumper.res' => true,
            'translation.dumper.xliff' => true,
            'translation.dumper.yml' => true,
            'translation.extractor' => true,
            'translation.extractor.php' => true,
            'translation.loader' => true,
            'translation.loader.csv' => true,
            'translation.loader.dat' => true,
            'translation.loader.ini' => true,
            'translation.loader.json' => true,
            'translation.loader.mo' => true,
            'translation.loader.php' => true,
            'translation.loader.po' => true,
            'translation.loader.qt' => true,
            'translation.loader.res' => true,
            'translation.loader.xliff' => true,
            'translation.loader.yml' => true,
            'translation.reader' => true,
            'translation.warmer' => true,
            'translation.writer' => true,
            'translator.default' => true,
            'translator_listener' => true,
            'twig.cache_warmer' => true,
            'twig.command.debug' => true,
            'twig.command.lint' => true,
            'twig.exception_listener' => true,
            'twig.extension.craue_formflow' => true,
            'twig.extension.routing' => true,
            'twig.form.renderer' => true,
            'twig.loader' => true,
            'twig.loader.filesystem' => true,
            'twig.profile' => true,
            'twig.runtime.httpkernel' => true,
            'twig.template_cache_warmer' => true,
            'twig.translation.extractor' => true,
            'uri_signer' => true,
            'validate_request_listener' => true,
            'validator.builder' => true,
            'validator.email' => true,
            'validator.expression' => true,
            'validator.mapping.cache_warmer' => true,
            'web_profiler.csp.handler' => true,
            'web_profiler.debug_toolbar' => true,
            'webpack_encore.entrypoint_lookup.cache_warmer' => true,
            'webpack_encore.entrypoint_lookup[_default]' => true,
            'webpack_encore.entrypoint_lookup_collection' => true,
            'webpack_encore.exception_listener' => true,
            'webpack_encore.tag_renderer' => true,
        ];
        $this->aliases = [
            'Doctrine\\DBAL\\Connection' => 'doctrine.dbal.default_connection',
            'Doctrine\\ORM\\EntityManagerInterface' => 'doctrine.orm.default_entity_manager',
            'FOS\\RestBundle\\Request\\ParamFetcherInterface' => 'fos_rest.request.param_fetcher',
            'League\\Tactician\\CommandBus' => 'tactician.commandbus.default',
            'Rialto\\Database\\Orm\\DbManager' => 'Doctrine\\Common\\Persistence\\ObjectManager',
            'Rialto\\Payment\\PaymentGateway' => 'Rialto\\Payment\\AuthorizeNet',
            'Symfony\\Component\\EventDispatcher\\EventDispatcherInterface' => 'debug.event_dispatcher',
            'Symfony\\Component\\Form\\FormFactoryInterface' => 'form.factory',
            'Symfony\\Component\\HttpFoundation\\RequestStack' => 'request_stack',
            'Symfony\\Component\\HttpFoundation\\Session\\Session' => 'session',
            'Symfony\\Component\\HttpFoundation\\Session\\SessionInterface' => 'session',
            'Symfony\\Component\\Security\\Core\\Authorization\\AuthorizationCheckerInterface' => 'security.authorization_checker',
            'Symfony\\Component\\Validator\\Validator\\ValidatorInterface' => 'validator',
            'Symfony\\WebpackEncoreBundle\\Asset\\EntrypointLookupInterface' => 'webpack_encore.entrypoint_lookup[_default]',
            'Twig_Environment' => 'twig',
            'cache.app_clearer' => 'cache.default_clearer',
            'console.command.doctrine_bundle_doctrinecachebundle_command_containscommand' => 'doctrine_cache.contains_command',
            'console.command.doctrine_bundle_doctrinecachebundle_command_deletecommand' => 'doctrine_cache.delete_command',
            'console.command.doctrine_bundle_doctrinecachebundle_command_flushcommand' => 'doctrine_cache.flush_command',
            'console.command.doctrine_bundle_doctrinecachebundle_command_statscommand' => 'doctrine_cache.stats_command',
            'console.command.doctrine_bundle_migrationsbundle_command_migrationsdiffdoctrinecommand' => 'doctrine_migrations.diff_command',
            'console.command.doctrine_bundle_migrationsbundle_command_migrationsexecutedoctrinecommand' => 'doctrine_migrations.execute_command',
            'console.command.doctrine_bundle_migrationsbundle_command_migrationsgeneratedoctrinecommand' => 'doctrine_migrations.generate_command',
            'console.command.doctrine_bundle_migrationsbundle_command_migrationslatestdoctrinecommand' => 'doctrine_migrations.latest_command',
            'console.command.doctrine_bundle_migrationsbundle_command_migrationsmigratedoctrinecommand' => 'doctrine_migrations.migrate_command',
            'console.command.doctrine_bundle_migrationsbundle_command_migrationsstatusdoctrinecommand' => 'doctrine_migrations.status_command',
            'console.command.doctrine_bundle_migrationsbundle_command_migrationsversiondoctrinecommand' => 'doctrine_migrations.version_command',
            'console.command.league_tactician_bundle_command_debugcommand' => 'tactician.command.debug',
            'database_connection' => 'doctrine.dbal.default_connection',
            'doctrine.orm.default_metadata_cache' => 'doctrine_cache.providers.doctrine.orm.default_metadata_cache',
            'doctrine.orm.default_query_cache' => 'doctrine_cache.providers.doctrine.orm.default_query_cache',
            'doctrine.orm.default_result_cache' => 'doctrine_cache.providers.doctrine.orm.default_result_cache',
            'doctrine.orm.entity_manager' => 'doctrine.orm.default_entity_manager',
            'easy_admin.property_accessor' => 'property_accessor',
            'event_dispatcher' => 'debug.event_dispatcher',
            'fos_rest.router' => 'Symfony\\Component\\Routing\\RouterInterface',
            'fos_rest.serializer.jms_handler_registry' => 'jms_serializer.handler_registry',
            'fos_rest.templating' => 'templating',
            'gumstix_rest.access_denied_handler' => 'Gumstix\\RestBundle\\Handler\\AccessDeniedHandler',
            'gumstix_rest.form_error_normalizer' => 'Gumstix\\RestBundle\\Serializer\\FormErrorNormalizer',
            'gumstix_sso.cookie_auth' => 'Gumstix\\SSOBundle\\Security\\CookieAuthenticator',
            'gumstix_sso.credential_storage' => 'Gumstix\\SSO\\Service\\CredentialStorage',
            'gumstix_sso.credential_storage_file' => 'Gumstix\\SSO\\Service\\CredentialStorage',
            'gumstix_sso.header_auth' => 'Gumstix\\SSOBundle\\Security\\HeaderAuthenticator',
            'gumstix_sso.http_client_factory' => 'Gumstix\\SSOBundle\\Service\\HttpClientFactory',
            'gumstix_sso.login_auth' => 'Gumstix\\SSOBundle\\Security\\LoginAuthenticator',
            'gumstix_sso.logout_service' => 'Gumstix\\SSOBundle\\Service\\LogoutService',
            'gumstix_sso.sso' => 'Gumstix\\SSO\\Service\\SingleSignOn',
            'gumstix_sso.sso_factory' => 'Gumstix\\SSOBundle\\Service\\SingleSignOnFactory',
            'gumstix_sso.twig_extension' => 'Gumstix\\SSOBundle\\Twig\\SSOExtension',
            'jms_serializer' => 'JMS\\Serializer\\SerializerInterface',
            'jms_serializer.form_error_handler' => 'fos_rest.serializer.form_error_handler',
            'mailer' => 'swiftmailer.mailer.default',
            'router' => 'Symfony\\Component\\Routing\\RouterInterface',
            'security.authentication.session_strategy.magento2_oauth_callback' => 'security.authentication.session_strategy.shopify_webhook',
            'security.authentication.session_strategy.main' => 'security.authentication.session_strategy.api',
            'session.storage' => 'session.storage.native',
            'swiftmailer.mailer' => 'swiftmailer.mailer.default',
            'swiftmailer.mailer.default.transport' => 'Rialto\\Email\\FakeTransport',
            'swiftmailer.mailer.transport.fake_transport' => 'Rialto\\Email\\FakeTransport',
            'swiftmailer.plugin.messagelogger' => 'swiftmailer.mailer.default.plugin.messagelogger',
            'swiftmailer.transport' => 'Rialto\\Email\\FakeTransport',
            'tactician.commandbus' => 'tactician.commandbus.default',
            'tactician.handler.locator.symfony' => 'tactician.commandbus.default.handler.locator',
            'tactician.middleware.command_handler' => 'tactician.commandbus.default.middleware.command_handler',
            'tactician.middleware.doctrine' => 'tactician.middleware.doctrine.default',
            'tactician.middleware.doctrine_rollback_only' => 'tactician.middleware.doctrine_rollback_only.default',
        ];
    }

    public function getRemovedIds()
    {
        return require $this->containerDir.\DIRECTORY_SEPARATOR.'removed-ids.php';
    }

    public function compile()
    {
        throw new LogicException('You cannot compile a dumped container that was already compiled.');
    }

    public function isCompiled()
    {
        return true;
    }

    public function isFrozen()
    {
        @trigger_error(sprintf('The %s() method is deprecated since Symfony 3.3 and will be removed in 4.0. Use the isCompiled() method instead.', __METHOD__), E_USER_DEPRECATED);

        return true;
    }

    protected function load($file, $lazyLoad = true)
    {
        return require $this->containerDir.\DIRECTORY_SEPARATOR.$file;
    }

    protected function createProxy($class, \Closure $factory)
    {
        class_exists($class, false) || $this->load("{$class}.php");

        return $factory();
    }

    /**
     * Gets the public 'Doctrine\Common\Persistence\ObjectManager' shared autowired service.
     *
     * @return \Rialto\Database\Orm\ErpDbManager
     */
    protected function getObjectManagerService()
    {
        return $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] = new \Rialto\Database\Orm\ErpDbManager(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->getDoctrine_Orm_DefaultEntityManagerService()) && false ?: '_'});
    }

    /**
     * Gets the public 'Gumstix\Storage\FileStorage' shared autowired service.
     *
     * @return \Gumstix\Storage\FileStorage
     */
    protected function getFileStorageService()
    {
        return $this->services['Gumstix\\Storage\\FileStorage'] = \Gumstix\Storage\GaufretteStorage::awsS3(new \Aws\S3\S3Client(['region' => 'us-west-2', 'version' => 'latest']), 'devstix-rialto-files');
    }

    /**
     * Gets the public 'Rialto\Accounting\Web\AccountingRouter' shared autowired service.
     *
     * @return \Rialto\Accounting\Web\AccountingRouter
     */
    protected function getAccountingRouterService()
    {
        return $this->services['Rialto\\Accounting\\Web\\AccountingRouter'] = new \Rialto\Accounting\Web\AccountingRouter(${($_ = isset($this->services['Symfony\\Component\\Routing\\RouterInterface']) ? $this->services['Symfony\\Component\\Routing\\RouterInterface'] : $this->getRouterInterfaceService()) && false ?: '_'});
    }

    /**
     * Gets the public 'Rialto\Cms\CmsEngine' shared autowired service.
     *
     * @return \Rialto\Cms\CmsEngine
     */
    protected function getCmsEngineService()
    {
        return $this->services['Rialto\\Cms\\CmsEngine'] = new \Rialto\Cms\CmsEngine(${($_ = isset($this->services['Rialto\\Cms\\CmsLoader']) ? $this->services['Rialto\\Cms\\CmsLoader'] : $this->getCmsLoaderService()) && false ?: '_'}, [0 => ${($_ = isset($this->services['twig.extension.routing']) ? $this->services['twig.extension.routing'] : $this->getTwig_Extension_RoutingService()) && false ?: '_'}, 1 => ${($_ = isset($this->services['Gumstix\\GeographyBundle\\Twig\\GeographyExtension']) ? $this->services['Gumstix\\GeographyBundle\\Twig\\GeographyExtension'] : $this->getGeographyExtensionService()) && false ?: '_'}]);
    }

    /**
     * Gets the public 'Rialto\Filetype\Postscript\FontFilesystem' shared autowired service.
     *
     * @return \Rialto\Filetype\Postscript\FontFilesystem
     */
    protected function getFontFilesystemService()
    {
        return $this->services['Rialto\\Filetype\\Postscript\\FontFilesystem'] = new \Rialto\Filetype\Postscript\FontFilesystem(($this->targetDirs[4].'/fonts'));
    }

    /**
     * Gets the public 'Rialto\Manufacturing\Web\ManufacturingRouter' shared autowired service.
     *
     * @return \Rialto\Manufacturing\Web\ManufacturingRouter
     */
    protected function getManufacturingRouterService()
    {
        return $this->services['Rialto\\Manufacturing\\Web\\ManufacturingRouter'] = new \Rialto\Manufacturing\Web\ManufacturingRouter(${($_ = isset($this->services['Symfony\\Component\\Routing\\RouterInterface']) ? $this->services['Symfony\\Component\\Routing\\RouterInterface'] : $this->getRouterInterfaceService()) && false ?: '_'});
    }

    /**
     * Gets the public 'Rialto\Printing\Printer\PrintServer' autowired service.
     *
     * @return \Rialto\Printing\Printer\PrinterRepo
     */
    protected function getPrintServerService()
    {
        return ${($_ = isset($this->services['doctrine']) ? $this->services['doctrine'] : $this->getDoctrineService()) && false ?: '_'}->getRepository('Rialto\\Printing\\Printer\\Printer');
    }

    /**
     * Gets the public 'Rialto\Purchasing\Invoice\SupplierInvoiceFilesystem' shared autowired service.
     *
     * @return \Rialto\Purchasing\Invoice\SupplierInvoiceFilesystem
     */
    protected function getSupplierInvoiceFilesystemService()
    {
        return $this->services['Rialto\\Purchasing\\Invoice\\SupplierInvoiceFilesystem'] = new \Rialto\Purchasing\Invoice\SupplierInvoiceFilesystem(${($_ = isset($this->services['Gumstix\\Storage\\FileStorage']) ? $this->services['Gumstix\\Storage\\FileStorage'] : $this->getFileStorageService()) && false ?: '_'});
    }

    /**
     * Gets the public 'Rialto\Purchasing\Producer\CommitmentDateEstimator\StockProducerCommitmentDateEstimator' shared autowired service.
     *
     * @return \Rialto\Purchasing\Producer\CommitmentDateEstimator\StockProducerCommitmentDateEstimator
     */
    protected function getStockProducerCommitmentDateEstimatorService()
    {
        return $this->services['Rialto\\Purchasing\\Producer\\CommitmentDateEstimator\\StockProducerCommitmentDateEstimator'] = new \Rialto\Purchasing\Producer\CommitmentDateEstimator\StockProducerCommitmentDateEstimator(${($_ = isset($this->services['Rialto\\Shipping\\Method\\ShippingTimeEstimator\\ShippingTimeEstimatorInterface']) ? $this->services['Rialto\\Shipping\\Method\\ShippingTimeEstimator\\ShippingTimeEstimatorInterface'] : ($this->services['Rialto\\Shipping\\Method\\ShippingTimeEstimator\\ShippingTimeEstimatorInterface'] = new \Rialto\Shipping\Method\ShippingTimeEstimator\ShippingTimeEstimator())) && false ?: '_'});
    }

    /**
     * Gets the public 'Rialto\Purchasing\Web\PurchasingRouter' shared autowired service.
     *
     * @return \Rialto\Purchasing\Web\PurchasingRouter
     */
    protected function getPurchasingRouterService()
    {
        return $this->services['Rialto\\Purchasing\\Web\\PurchasingRouter'] = new \Rialto\Purchasing\Web\PurchasingRouter(${($_ = isset($this->services['Symfony\\Component\\Routing\\RouterInterface']) ? $this->services['Symfony\\Component\\Routing\\RouterInterface'] : $this->getRouterInterfaceService()) && false ?: '_'});
    }

    /**
     * Gets the public 'Rialto\Sales\Web\SalesRouter' shared autowired service.
     *
     * @return \Rialto\Sales\Web\SalesRouter
     */
    protected function getSalesRouterService()
    {
        return $this->services['Rialto\\Sales\\Web\\SalesRouter'] = new \Rialto\Sales\Web\SalesRouter(${($_ = isset($this->services['Symfony\\Component\\Routing\\RouterInterface']) ? $this->services['Symfony\\Component\\Routing\\RouterInterface'] : $this->getRouterInterfaceService()) && false ?: '_'});
    }

    /**
     * Gets the public 'Rialto\Shipping\Method\ShippingTimeEstimator\ShippingTimeEstimatorInterface' shared autowired service.
     *
     * @return \Rialto\Shipping\Method\ShippingTimeEstimator\ShippingTimeEstimator
     */
    protected function getShippingTimeEstimatorInterfaceService()
    {
        return $this->services['Rialto\\Shipping\\Method\\ShippingTimeEstimator\\ShippingTimeEstimatorInterface'] = new \Rialto\Shipping\Method\ShippingTimeEstimator\ShippingTimeEstimator();
    }

    /**
     * Gets the public 'Rialto\Stock\Web\StockRouter' shared autowired service.
     *
     * @return \Rialto\Stock\Web\StockRouter
     */
    protected function getStockRouterService()
    {
        return $this->services['Rialto\\Stock\\Web\\StockRouter'] = new \Rialto\Stock\Web\StockRouter(${($_ = isset($this->services['Symfony\\Component\\Routing\\RouterInterface']) ? $this->services['Symfony\\Component\\Routing\\RouterInterface'] : $this->getRouterInterfaceService()) && false ?: '_'});
    }

    /**
     * Gets the public 'Symfony\Component\Routing\RouterInterface' shared service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected function getRouterInterfaceService()
    {
        $this->services['Symfony\\Component\\Routing\\RouterInterface'] = $instance = new \Symfony\Bundle\FrameworkBundle\Routing\Router($this, ($this->targetDirs[4].'/app/config/routing_dev.yaml'), ['cache_dir' => $this->targetDirs[0], 'debug' => true, 'generator_class' => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator', 'generator_base_class' => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator', 'generator_dumper_class' => 'Symfony\\Component\\Routing\\Generator\\Dumper\\PhpGeneratorDumper', 'generator_cache_class' => 'appDevDebugProjectContainerUrlGenerator', 'matcher_class' => 'Symfony\\Bundle\\FrameworkBundle\\Routing\\RedirectableUrlMatcher', 'matcher_base_class' => 'Symfony\\Bundle\\FrameworkBundle\\Routing\\RedirectableUrlMatcher', 'matcher_dumper_class' => 'Symfony\\Component\\Routing\\Matcher\\Dumper\\PhpMatcherDumper', 'matcher_cache_class' => 'appDevDebugProjectContainerUrlMatcher', 'strict_requirements' => true], ${($_ = isset($this->services['router.request_context']) ? $this->services['router.request_context'] : $this->getRouter_RequestContextService()) && false ?: '_'});

        $instance->setConfigCacheFactory(${($_ = isset($this->services['config_cache_factory']) ? $this->services['config_cache_factory'] : $this->getConfigCacheFactoryService()) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the public 'craue_formflow_util' shared service.
     *
     * @return \Craue\FormFlowBundle\Util\FormFlowUtil
     */
    protected function getCraueFormflowUtilService()
    {
        return $this->services['craue_formflow_util'] = new \Craue\FormFlowBundle\Util\FormFlowUtil();
    }

    /**
     * Gets the public 'doctrine' shared service.
     *
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected function getDoctrineService()
    {
        return $this->services['doctrine'] = new \Doctrine\Bundle\DoctrineBundle\Registry($this, $this->parameters['doctrine.connections'], $this->parameters['doctrine.entity_managers'], 'default', 'default');
    }

    /**
     * Gets the public 'doctrine.dbal.default_connection' shared service.
     *
     * @return \Doctrine\DBAL\Connection
     */
    protected function getDoctrine_Dbal_DefaultConnectionService()
    {
        $a = new \Doctrine\DBAL\Configuration();

        $b = new \Doctrine\DBAL\Logging\LoggerChain();
        $b->addLogger(new \Symfony\Bridge\Doctrine\Logger\DbalLogger(${($_ = isset($this->services['monolog.logger.doctrine']) ? $this->services['monolog.logger.doctrine'] : $this->getMonolog_Logger_DoctrineService()) && false ?: '_'}, ${($_ = isset($this->services['debug.stopwatch']) ? $this->services['debug.stopwatch'] : ($this->services['debug.stopwatch'] = new \Symfony\Component\Stopwatch\Stopwatch(true))) && false ?: '_'}));
        $b->addLogger(new \Doctrine\DBAL\Logging\DebugStack());

        $a->setSQLLogger($b);
        $c = new \Symfony\Bridge\Doctrine\ContainerAwareEventManager($this);

        $d = new \Rialto\Entity\DomainEventHandler(${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->getDebug_EventDispatcherService()) && false ?: '_'});

        $c->addEventSubscriber(new \Rialto\Stock\Item\EventListener\DefaultWorkTypeListener());
        $c->addEventListener([0 => 'postPersist'], $d);
        $c->addEventListener([0 => 'postUpdate'], $d);
        $c->addEventListener([0 => 'postRemove'], $d);
        $c->addEventListener([0 => 'postFlush'], $d);
        $c->addEventListener([0 => 'preUpdate'], new \Rialto\Madison\Version\VersionChangeListener(${($_ = isset($this->services['Rialto\\Madison\\Version\\VersionChangeCache']) ? $this->services['Rialto\\Madison\\Version\\VersionChangeCache'] : ($this->services['Rialto\\Madison\\Version\\VersionChangeCache'] = new \Rialto\Madison\Version\VersionChangeCache())) && false ?: '_'}));
        $c->addEventListener([0 => 'postUpdate'], ${($_ = isset($this->services['Rialto\\Manufacturing\\Task\\ProductionTaskRefreshListener']) ? $this->services['Rialto\\Manufacturing\\Task\\ProductionTaskRefreshListener'] : $this->getProductionTaskRefreshListenerService()) && false ?: '_'});
        $c->addEventListener([0 => 'loadClassMetadata'], ${($_ = isset($this->services['doctrine.orm.default_listeners.attach_entity_listeners']) ? $this->services['doctrine.orm.default_listeners.attach_entity_listeners'] : ($this->services['doctrine.orm.default_listeners.attach_entity_listeners'] = new \Doctrine\ORM\Tools\AttachEntityListenersListener())) && false ?: '_'});
        $c->addEventListener([0 => 'postGenerateSchema'], 'jms_job_queue.entity.many_to_any_listener');
        $c->addEventListener([0 => 'postLoad'], 'jms_job_queue.entity.many_to_any_listener');
        $c->addEventListener([0 => 'postPersist'], 'jms_job_queue.entity.many_to_any_listener');
        $c->addEventListener([0 => 'preRemove'], 'jms_job_queue.entity.many_to_any_listener');
        $c->addEventListener([0 => 'postGenerateSchema'], 'jms_job_queue.entity.statistics_listener');

        return $this->services['doctrine.dbal.default_connection'] = ${($_ = isset($this->services['doctrine.dbal.connection_factory']) ? $this->services['doctrine.dbal.connection_factory'] : $this->getDoctrine_Dbal_ConnectionFactoryService()) && false ?: '_'}->createConnection(['driver' => 'pdo_mysql', 'host' => '127.0.0.1', 'dbname' => 'rialto', 'user' => 'rialto', 'password' => 'rialto', 'charset' => 'utf8mb4', 'port' => NULL, 'driverOptions' => [], 'serverVersion' => '5.6', 'defaultTableOptions' => []], $a, $c, []);
    }

    /**
     * Gets the public 'doctrine.orm.default_entity_manager' shared service.
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getDoctrine_Orm_DefaultEntityManagerService($lazyLoad = true)
    {
        if ($lazyLoad) {
            return $this->services['doctrine.orm.default_entity_manager'] = $this->createProxy('EntityManager_9a5be93', function () {
                return \EntityManager_9a5be93::staticProxyConstructor(function (&$wrappedInstance, \ProxyManager\Proxy\LazyLoadingInterface $proxy) {
                    $wrappedInstance = $this->getDoctrine_Orm_DefaultEntityManagerService(false);

                    $proxy->setProxyInitializer(null);

                    return true;
                });
            });
        }

        $a = new \Doctrine\ORM\Configuration();

        $b = new \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain();

        $c = new \Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver([($this->targetDirs[4].'/app/config/doctrine') => 'Rialto']);
        $c->setGlobalBasename('mapping');

        $b->addDriver($c, 'Rialto');
        $b->addDriver(new \Doctrine\ORM\Mapping\Driver\AnnotationDriver(${($_ = isset($this->services['annotation_reader']) ? $this->services['annotation_reader'] : $this->getAnnotationReaderService()) && false ?: '_'}, [0 => ($this->targetDirs[4].'/vendor/jms/job-queue-bundle/JMS/JobQueueBundle/Entity')]), 'JMS\\JobQueueBundle\\Entity');

        $a->setEntityNamespaces(['Rialto' => 'Rialto', 'JMSJobQueueBundle' => 'JMS\\JobQueueBundle\\Entity']);
        $a->setMetadataCacheImpl(${($_ = isset($this->services['doctrine_cache.providers.doctrine.orm.default_metadata_cache']) ? $this->services['doctrine_cache.providers.doctrine.orm.default_metadata_cache'] : $this->getDoctrineCache_Providers_Doctrine_Orm_DefaultMetadataCacheService()) && false ?: '_'});
        $a->setQueryCacheImpl(${($_ = isset($this->services['doctrine_cache.providers.doctrine.orm.default_query_cache']) ? $this->services['doctrine_cache.providers.doctrine.orm.default_query_cache'] : $this->getDoctrineCache_Providers_Doctrine_Orm_DefaultQueryCacheService()) && false ?: '_'});
        $a->setResultCacheImpl(${($_ = isset($this->services['doctrine_cache.providers.doctrine.orm.default_result_cache']) ? $this->services['doctrine_cache.providers.doctrine.orm.default_result_cache'] : $this->getDoctrineCache_Providers_Doctrine_Orm_DefaultResultCacheService()) && false ?: '_'});
        $a->setMetadataDriverImpl($b);
        $a->setProxyDir(($this->targetDirs[0].'/doctrine/orm/Proxies'));
        $a->setProxyNamespace('Proxies');
        $a->setAutoGenerateProxyClasses(true);
        $a->setClassMetadataFactoryName('Doctrine\\ORM\\Mapping\\ClassMetadataFactory');
        $a->setDefaultRepositoryClassName('Doctrine\\ORM\\EntityRepository');
        $a->setNamingStrategy(new \Doctrine\ORM\Mapping\DefaultNamingStrategy());
        $a->setQuoteStrategy(new \Doctrine\ORM\Mapping\DefaultQuoteStrategy());
        $a->setEntityListenerResolver(${($_ = isset($this->services['doctrine.orm.default_entity_listener_resolver']) ? $this->services['doctrine.orm.default_entity_listener_resolver'] : ($this->services['doctrine.orm.default_entity_listener_resolver'] = new \Doctrine\Bundle\DoctrineBundle\Mapping\ContainerEntityListenerResolver($this))) && false ?: '_'});
        $a->setRepositoryFactory(new \Doctrine\Bundle\DoctrineBundle\Repository\ContainerRepositoryFactory(new \Symfony\Component\DependencyInjection\ServiceLocator([])));
        $a->addCustomStringFunction('IFNULL', 'DoctrineExtensions\\Query\\Mysql\\IfNull');
        $a->addCustomStringFunction('IF', 'DoctrineExtensions\\Query\\Mysql\\IfElse');
        $a->addCustomStringFunction('GROUP_CONCAT', 'DoctrineExtensions\\Query\\Mysql\\GroupConcat');
        $a->addCustomStringFunction('REPLACE', 'DoctrineExtensions\\Query\\Mysql\\Replace');
        $a->addCustomDatetimeFunction('DATE', 'DoctrineExtensions\\Query\\Mysql\\Date');
        $a->addCustomDatetimeFunction('TIMESTAMPDIFF', 'DoctrineExtensions\\Query\\Mysql\\TimestampDiff');

        $instance = \Doctrine\ORM\EntityManager::create(${($_ = isset($this->services['doctrine.dbal.default_connection']) ? $this->services['doctrine.dbal.default_connection'] : $this->getDoctrine_Dbal_DefaultConnectionService()) && false ?: '_'}, $a);

        ${($_ = isset($this->services['doctrine.orm.default_manager_configurator']) ? $this->services['doctrine.orm.default_manager_configurator'] : ($this->services['doctrine.orm.default_manager_configurator'] = new \Doctrine\Bundle\DoctrineBundle\ManagerConfigurator([], []))) && false ?: '_'}->configure($instance);

        return $instance;
    }

    /**
     * Gets the public 'doctrine_cache.providers.doctrine.orm.default_metadata_cache' shared service.
     *
     * @return \Doctrine\Common\Cache\ArrayCache
     */
    protected function getDoctrineCache_Providers_Doctrine_Orm_DefaultMetadataCacheService()
    {
        $this->services['doctrine_cache.providers.doctrine.orm.default_metadata_cache'] = $instance = new \Doctrine\Common\Cache\ArrayCache();

        $instance->setNamespace('sf_orm_default_f22bd84df7c3c7473b62847c8267cc57a811b05861142659adba14036bbc7d6f');

        return $instance;
    }

    /**
     * Gets the public 'doctrine_cache.providers.doctrine.orm.default_query_cache' shared service.
     *
     * @return \Doctrine\Common\Cache\ArrayCache
     */
    protected function getDoctrineCache_Providers_Doctrine_Orm_DefaultQueryCacheService()
    {
        $this->services['doctrine_cache.providers.doctrine.orm.default_query_cache'] = $instance = new \Doctrine\Common\Cache\ArrayCache();

        $instance->setNamespace('sf_orm_default_f22bd84df7c3c7473b62847c8267cc57a811b05861142659adba14036bbc7d6f');

        return $instance;
    }

    /**
     * Gets the public 'doctrine_cache.providers.doctrine.orm.default_result_cache' shared service.
     *
     * @return \Doctrine\Common\Cache\ArrayCache
     */
    protected function getDoctrineCache_Providers_Doctrine_Orm_DefaultResultCacheService()
    {
        $this->services['doctrine_cache.providers.doctrine.orm.default_result_cache'] = $instance = new \Doctrine\Common\Cache\ArrayCache();

        $instance->setNamespace('sf_orm_default_f22bd84df7c3c7473b62847c8267cc57a811b05861142659adba14036bbc7d6f');

        return $instance;
    }

    /**
     * Gets the public 'easyadmin.cache.manager' shared service.
     *
     * @return \EasyCorp\Bundle\EasyAdminBundle\Cache\CacheManager
     */
    protected function getEasyadmin_Cache_ManagerService()
    {
        return $this->services['easyadmin.cache.manager'] = new \EasyCorp\Bundle\EasyAdminBundle\Cache\CacheManager(($this->targetDirs[0].'/easy_admin'));
    }

    /**
     * Gets the public 'easyadmin.config.manager' shared service.
     *
     * @return \EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigManager
     */
    protected function getEasyadmin_Config_ManagerService()
    {
        $this->services['easyadmin.config.manager'] = $instance = new \EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigManager(${($_ = isset($this->services['easyadmin.cache.manager']) ? $this->services['easyadmin.cache.manager'] : ($this->services['easyadmin.cache.manager'] = new \EasyCorp\Bundle\EasyAdminBundle\Cache\CacheManager(($this->targetDirs[0].'/easy_admin')))) && false ?: '_'}, ${($_ = isset($this->services['property_accessor']) ? $this->services['property_accessor'] : $this->getPropertyAccessorService()) && false ?: '_'}, $this->parameters['easyadmin.config'], true);

        $instance->addConfigPass(new \EasyCorp\Bundle\EasyAdminBundle\Configuration\NormalizerConfigPass($this));
        $instance->addConfigPass(new \EasyCorp\Bundle\EasyAdminBundle\Configuration\DesignConfigPass($this, true, 'en'));
        $instance->addConfigPass(new \EasyCorp\Bundle\EasyAdminBundle\Configuration\MenuConfigPass());
        $instance->addConfigPass(new \EasyCorp\Bundle\EasyAdminBundle\Configuration\ActionConfigPass());
        $instance->addConfigPass(new \EasyCorp\Bundle\EasyAdminBundle\Configuration\MetadataConfigPass(${($_ = isset($this->services['doctrine']) ? $this->services['doctrine'] : $this->getDoctrineService()) && false ?: '_'}));
        $instance->addConfigPass(new \EasyCorp\Bundle\EasyAdminBundle\Configuration\PropertyConfigPass(${($_ = isset($this->services['form.registry']) ? $this->services['form.registry'] : $this->getForm_RegistryService()) && false ?: '_'}));
        $instance->addConfigPass(new \EasyCorp\Bundle\EasyAdminBundle\Configuration\ViewConfigPass());
        $instance->addConfigPass(new \EasyCorp\Bundle\EasyAdminBundle\Configuration\TemplateConfigPass(${($_ = isset($this->services['twig.loader.filesystem']) ? $this->services['twig.loader.filesystem'] : $this->getTwig_Loader_FilesystemService()) && false ?: '_'}));
        $instance->addConfigPass(new \EasyCorp\Bundle\EasyAdminBundle\Configuration\DefaultConfigPass());

        return $instance;
    }

    /**
     * Gets the public 'easyadmin.listener.controller' shared service.
     *
     * @return \EasyCorp\Bundle\EasyAdminBundle\EventListener\ControllerListener
     */
    protected function getEasyadmin_Listener_ControllerService()
    {
        return $this->services['easyadmin.listener.controller'] = new \EasyCorp\Bundle\EasyAdminBundle\EventListener\ControllerListener(${($_ = isset($this->services['easyadmin.config.manager']) ? $this->services['easyadmin.config.manager'] : $this->getEasyadmin_Config_ManagerService()) && false ?: '_'}, ${($_ = isset($this->services['debug.controller_resolver']) ? $this->services['debug.controller_resolver'] : $this->getDebug_ControllerResolverService()) && false ?: '_'});
    }

    /**
     * Gets the public 'easyadmin.router' shared service.
     *
     * @return \EasyCorp\Bundle\EasyAdminBundle\Router\EasyAdminRouter
     */
    protected function getEasyadmin_RouterService()
    {
        return $this->services['easyadmin.router'] = new \EasyCorp\Bundle\EasyAdminBundle\Router\EasyAdminRouter(${($_ = isset($this->services['easyadmin.config.manager']) ? $this->services['easyadmin.config.manager'] : $this->getEasyadmin_Config_ManagerService()) && false ?: '_'}, ${($_ = isset($this->services['Symfony\\Component\\Routing\\RouterInterface']) ? $this->services['Symfony\\Component\\Routing\\RouterInterface'] : $this->getRouterInterfaceService()) && false ?: '_'}, ${($_ = isset($this->services['property_accessor']) ? $this->services['property_accessor'] : $this->getPropertyAccessorService()) && false ?: '_'}, ${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : ($this->services['request_stack'] = new \Symfony\Component\HttpFoundation\RequestStack())) && false ?: '_'});
    }

    /**
     * Gets the public 'http_kernel' shared service.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernel
     */
    protected function getHttpKernelService()
    {
        return $this->services['http_kernel'] = new \Symfony\Component\HttpKernel\HttpKernel(${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->getDebug_EventDispatcherService()) && false ?: '_'}, ${($_ = isset($this->services['debug.controller_resolver']) ? $this->services['debug.controller_resolver'] : $this->getDebug_ControllerResolverService()) && false ?: '_'}, ${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : ($this->services['request_stack'] = new \Symfony\Component\HttpFoundation\RequestStack())) && false ?: '_'}, ${($_ = isset($this->services['debug.argument_resolver']) ? $this->services['debug.argument_resolver'] : $this->getDebug_ArgumentResolverService()) && false ?: '_'});
    }

    /**
     * Gets the public 'request_stack' shared service.
     *
     * @return \Symfony\Component\HttpFoundation\RequestStack
     */
    protected function getRequestStackService()
    {
        return $this->services['request_stack'] = new \Symfony\Component\HttpFoundation\RequestStack();
    }

    /**
     * Gets the public 'security.authorization_checker' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authorization\AuthorizationChecker
     */
    protected function getSecurity_AuthorizationCheckerService()
    {
        return $this->services['security.authorization_checker'] = new \Symfony\Component\Security\Core\Authorization\AuthorizationChecker(${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : ($this->services['security.token_storage'] = new \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage())) && false ?: '_'}, ${($_ = isset($this->services['security.authentication.manager']) ? $this->services['security.authentication.manager'] : $this->getSecurity_Authentication_ManagerService()) && false ?: '_'}, ${($_ = isset($this->services['debug.security.access.decision_manager']) ? $this->services['debug.security.access.decision_manager'] : $this->getDebug_Security_Access_DecisionManagerService()) && false ?: '_'}, false);
    }

    /**
     * Gets the public 'security.token_storage' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage
     */
    protected function getSecurity_TokenStorageService()
    {
        return $this->services['security.token_storage'] = new \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage();
    }

    /**
     * Gets the public 'translator' shared service.
     *
     * @return \Symfony\Component\Translation\LoggingTranslator
     */
    protected function getTranslatorService()
    {
        return $this->services['translator'] = new \Symfony\Component\Translation\LoggingTranslator(${($_ = isset($this->services['translator.default']) ? $this->services['translator.default'] : $this->getTranslator_DefaultService()) && false ?: '_'}, ${($_ = isset($this->services['monolog.logger.translation']) ? $this->services['monolog.logger.translation'] : $this->getMonolog_Logger_TranslationService()) && false ?: '_'});
    }

    /**
     * Gets the public 'twig' shared service.
     *
     * @return \Twig\Environment
     */
    protected function getTwigService()
    {
        $this->services['twig'] = $instance = new \Twig\Environment(${($_ = isset($this->services['twig.loader']) ? $this->services['twig.loader'] : $this->getTwig_LoaderService()) && false ?: '_'}, ['debug' => true, 'strict_variables' => true, 'exception_controller' => 'FOS\\RestBundle\\Controller\\ExceptionController::showAction', 'paths' => [($this->targetDirs[4].'/app/../templates') => NULL], 'form_themes' => $this->parameters['twig.form.resources'], 'autoescape' => 'name', 'cache' => ($this->targetDirs[0].'/twig'), 'charset' => 'UTF-8', 'default_path' => ($this->targetDirs[4].'/templates'), 'date' => ['format' => 'F j, Y H:i', 'interval_format' => '%d days', 'timezone' => NULL], 'number_format' => ['decimals' => 0, 'decimal_point' => '.', 'thousands_separator' => ',']]);

        $a = ${($_ = isset($this->services['security.logout_url_generator']) ? $this->services['security.logout_url_generator'] : $this->getSecurity_LogoutUrlGeneratorService()) && false ?: '_'};
        $b = ${($_ = isset($this->services['security.authorization_checker']) ? $this->services['security.authorization_checker'] : $this->getSecurity_AuthorizationCheckerService()) && false ?: '_'};
        $c = ${($_ = isset($this->services['debug.stopwatch']) ? $this->services['debug.stopwatch'] : ($this->services['debug.stopwatch'] = new \Symfony\Component\Stopwatch\Stopwatch(true))) && false ?: '_'};
        $d = ${($_ = isset($this->services['debug.file_link_formatter']) ? $this->services['debug.file_link_formatter'] : $this->getDebug_FileLinkFormatterService()) && false ?: '_'};
        $e = ${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : ($this->services['request_stack'] = new \Symfony\Component\HttpFoundation\RequestStack())) && false ?: '_'};
        $f = ${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'};
        $g = ${($_ = isset($this->services['Symfony\\Component\\Routing\\RouterInterface']) ? $this->services['Symfony\\Component\\Routing\\RouterInterface'] : $this->getRouterInterfaceService()) && false ?: '_'};
        $h = ${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : ($this->services['security.token_storage'] = new \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage())) && false ?: '_'};
        $i = new \Symfony\Component\VarDumper\Dumper\HtmlDumper(NULL, 'UTF-8', 1);
        $i->setDisplayOptions(['maxStringLength' => 4096, 'fileLinkFormat' => $d]);
        $j = new \Symfony\Bridge\Twig\AppVariable();
        $j->setEnvironment('dev');
        $j->setDebug(true);
        if ($this->has('security.token_storage')) {
            $j->setTokenStorage($h);
        }
        if ($this->has('request_stack')) {
            $j->setRequestStack($e);
        }

        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\LogoutUrlExtension($a));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\SecurityExtension($b));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\ProfilerExtension(${($_ = isset($this->services['twig.profile']) ? $this->services['twig.profile'] : ($this->services['twig.profile'] = new \Twig\Profiler\Profile())) && false ?: '_'}, $c));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\TranslationExtension(${($_ = isset($this->services['translator']) ? $this->services['translator'] : $this->getTranslatorService()) && false ?: '_'}));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\AssetExtension(${($_ = isset($this->services['assets.packages']) ? $this->services['assets.packages'] : $this->getAssets_PackagesService()) && false ?: '_'}));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\CodeExtension($d, ($this->targetDirs[4].'/app'), 'UTF-8'));
        $instance->addExtension(${($_ = isset($this->services['twig.extension.routing']) ? $this->services['twig.extension.routing'] : $this->getTwig_Extension_RoutingService()) && false ?: '_'});
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\YamlExtension());
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\StopwatchExtension($c, true));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\ExpressionExtension());
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\HttpKernelExtension());
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\HttpFoundationExtension($e, ${($_ = isset($this->services['router.request_context']) ? $this->services['router.request_context'] : $this->getRouter_RequestContextService()) && false ?: '_'}));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\FormExtension([0 => $this, 1 => 'twig.form.renderer']));
        $instance->addExtension(new \Rialto\Accounting\Web\AccountingExtension($f, ${($_ = isset($this->services['Rialto\\Accounting\\Web\\AccountingRouter']) ? $this->services['Rialto\\Accounting\\Web\\AccountingRouter'] : $this->getAccountingRouterService()) && false ?: '_'}));
        $instance->addExtension(new \Rialto\Allocation\Web\AllocationExtension($b, $g, ${($_ = isset($this->services['validator']) ? $this->services['validator'] : $this->getValidatorService()) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Allocation\\EstimatedArrivalDate\\EstimatedArrivalDateGenerator']) ? $this->services['Rialto\\Allocation\\EstimatedArrivalDate\\EstimatedArrivalDateGenerator'] : $this->getEstimatedArrivalDateGeneratorService()) && false ?: '_'}));
        $instance->addExtension(new \Rialto\Catalina\Web\CatalinaExtension('http://catalina.mystix.com'));
        $instance->addExtension(new \Rialto\Cms\CmsExtension(${($_ = isset($this->services['Rialto\\Cms\\CmsEngine']) ? $this->services['Rialto\\Cms\\CmsEngine'] : $this->getCmsEngineService()) && false ?: '_'}));
        $instance->addExtension(new \Rialto\Email\Web\EmailExtension());
        $instance->addExtension(new \Rialto\Filetype\FiletypeExtension(new \Rialto\Filetype\Image\QrCodeGenerator()));
        $instance->addExtension(new \Rialto\Filetype\Pdf\LatexExtension());
        $instance->addExtension(new \Rialto\Manufacturing\Web\ManufacturingExtension($f, ${($_ = isset($this->services['Rialto\\Manufacturing\\Web\\ManufacturingRouter']) ? $this->services['Rialto\\Manufacturing\\Web\\ManufacturingRouter'] : $this->getManufacturingRouterService()) && false ?: '_'}, $b));
        $instance->addExtension(new \Rialto\Purchasing\Web\PurchasingExtension($f, ${($_ = isset($this->services['Rialto\\Purchasing\\Web\\PurchasingRouter']) ? $this->services['Rialto\\Purchasing\\Web\\PurchasingRouter'] : $this->getPurchasingRouterService()) && false ?: '_'}, $b, ${($_ = isset($this->services['Rialto\\Purchasing\\Invoice\\SupplierInvoiceFilesystem']) ? $this->services['Rialto\\Purchasing\\Invoice\\SupplierInvoiceFilesystem'] : $this->getSupplierInvoiceFilesystemService()) && false ?: '_'}));
        $instance->addExtension(new \Rialto\Sales\Web\SalesExtension(${($_ = isset($this->services['Rialto\\Sales\\Web\\SalesRouter']) ? $this->services['Rialto\\Sales\\Web\\SalesRouter'] : $this->getSalesRouterService()) && false ?: '_'}, $f));
        $instance->addExtension(new \Rialto\Security\Web\SecurityExtension($h));
        $instance->addExtension(new \Rialto\Stock\Web\StockExtension($f, ${($_ = isset($this->services['Rialto\\Stock\\Web\\StockRouter']) ? $this->services['Rialto\\Stock\\Web\\StockRouter'] : $this->getStockRouterService()) && false ?: '_'}, $b));
        $instance->addExtension(new \Rialto\Summary\Menu\Web\SummaryExtension());
        $instance->addExtension(new \Rialto\Supplier\Web\SupplierExtension($f));
        $instance->addExtension(new \Rialto\Tax\Web\TaxExtension());
        $instance->addExtension(new \Rialto\Time\Web\TimeExtension());
        $instance->addExtension(new \Rialto\Ups\Shipping\Webservice\UpsExtension());
        $instance->addExtension(new \Rialto\Ups\TrackingRecord\Web\TrackingExtension(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->getDoctrine_Orm_DefaultEntityManagerService()) && false ?: '_'}));
        $instance->addExtension(new \Rialto\Web\RialtoExtension($g, $f));
        $instance->addExtension(new \Rialto\Web\NumberExtension());
        $instance->addExtension(new \Rialto\Web\Form\FormExtension(${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->getDebug_EventDispatcherService()) && false ?: '_'}));
        $instance->addExtension(new \Twig\Extension\DebugExtension());
        $instance->addExtension(${($_ = isset($this->services['twig.extension.craue_formflow']) ? $this->services['twig.extension.craue_formflow'] : $this->getTwig_Extension_CraueFormflowService()) && false ?: '_'});
        $instance->addExtension(new \Doctrine\Bundle\DoctrineBundle\Twig\DoctrineExtension());
        $instance->addExtension(new \EasyCorp\Bundle\EasyAdminBundle\Twig\EasyAdminTwigExtension(${($_ = isset($this->services['easyadmin.config.manager']) ? $this->services['easyadmin.config.manager'] : $this->getEasyadmin_Config_ManagerService()) && false ?: '_'}, ${($_ = isset($this->services['property_accessor']) ? $this->services['property_accessor'] : $this->getPropertyAccessorService()) && false ?: '_'}, ${($_ = isset($this->services['easyadmin.router']) ? $this->services['easyadmin.router'] : $this->getEasyadmin_RouterService()) && false ?: '_'}, true, $a));
        $instance->addExtension(${($_ = isset($this->services['jms_job_queue.twig.extension']) ? $this->services['jms_job_queue.twig.extension'] : ($this->services['jms_job_queue.twig.extension'] = new \JMS\JobQueueBundle\Twig\JobQueueExtension([]))) && false ?: '_'});
        $instance->addExtension(new \JMS\Serializer\Twig\SerializerRuntimeExtension());
        $instance->addExtension(${($_ = isset($this->services['gumstix_form.twig_extension']) ? $this->services['gumstix_form.twig_extension'] : ($this->services['gumstix_form.twig_extension'] = new \Gumstix\FormBundle\Twig\FormExtension())) && false ?: '_'});
        $instance->addExtension(${($_ = isset($this->services['Gumstix\\GeographyBundle\\Twig\\GeographyExtension']) ? $this->services['Gumstix\\GeographyBundle\\Twig\\GeographyExtension'] : $this->getGeographyExtensionService()) && false ?: '_'});
        $instance->addExtension(${($_ = isset($this->services['Gumstix\\SSOBundle\\Twig\\SSOExtension']) ? $this->services['Gumstix\\SSOBundle\\Twig\\SSOExtension'] : $this->getSSOExtensionService()) && false ?: '_'});
        $instance->addExtension(new \Symfony\WebpackEncoreBundle\Twig\EntryFilesTwigExtension(new \Symfony\Component\DependencyInjection\ServiceLocator(['webpack_encore.entrypoint_lookup_collection' => function () {
            return ${($_ = isset($this->services['webpack_encore.entrypoint_lookup_collection']) ? $this->services['webpack_encore.entrypoint_lookup_collection'] : $this->load('getWebpackEncore_EntrypointLookupCollectionService.php')) && false ?: '_'};
        }, 'webpack_encore.tag_renderer' => function () {
            return ${($_ = isset($this->services['webpack_encore.tag_renderer']) ? $this->services['webpack_encore.tag_renderer'] : $this->load('getWebpackEncore_TagRendererService.php')) && false ?: '_'};
        }])));
        $instance->addExtension(new \Symfony\Bundle\WebProfilerBundle\Twig\WebProfilerExtension($i));
        $instance->addGlobal('app', $j);
        $instance->addRuntimeLoader(new \Twig\RuntimeLoader\ContainerRuntimeLoader(new \Symfony\Component\DependencyInjection\ServiceLocator(['JMS\\Serializer\\Twig\\SerializerRuntimeHelper' => function () {
            return ${($_ = isset($this->services['jms_serializer.twig_extension.serializer_runtime_helper']) ? $this->services['jms_serializer.twig_extension.serializer_runtime_helper'] : $this->load('getJmsSerializer_TwigExtension_SerializerRuntimeHelperService.php')) && false ?: '_'};
        }, 'Symfony\\Bridge\\Twig\\Extension\\HttpKernelRuntime' => function () {
            return ${($_ = isset($this->services['twig.runtime.httpkernel']) ? $this->services['twig.runtime.httpkernel'] : $this->load('getTwig_Runtime_HttpkernelService.php')) && false ?: '_'};
        }, 'Symfony\\Component\\Form\\FormRenderer' => function () {
            return ${($_ = isset($this->services['twig.form.renderer']) ? $this->services['twig.form.renderer'] : $this->load('getTwig_Form_RendererService.php')) && false ?: '_'};
        }])));
        $instance->addGlobal('dojo_base', '//ajax.googleapis.com/ajax/libs/dojo/1.11.2');
        $instance->addGlobal('latex_image_path', ($this->targetDirs[4].'/app/Resources/latex'));
        $instance->addGlobal('bugtracker', ['uri' => 'https://mantis.gumstix.com', 'default_category' => 1, 'project_id' => 1]);
        (new \Symfony\Bundle\TwigBundle\DependencyInjection\Configurator\EnvironmentConfigurator('F j, Y H:i', '%d days', NULL, 0, '.', ','))->configure($instance);

        return $instance;
    }

    /**
     * Gets the public 'validator' shared service.
     *
     * @return \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    protected function getValidatorService()
    {
        return $this->services['validator'] = ${($_ = isset($this->services['validator.builder']) ? $this->services['validator.builder'] : $this->getValidator_BuilderService()) && false ?: '_'}->getValidator();
    }

    /**
     * Gets the private 'Gumstix\GeographyBundle\Twig\GeographyExtension' shared autowired service.
     *
     * @return \Gumstix\GeographyBundle\Twig\GeographyExtension
     */
    protected function getGeographyExtensionService()
    {
        return $this->services['Gumstix\\GeographyBundle\\Twig\\GeographyExtension'] = new \Gumstix\GeographyBundle\Twig\GeographyExtension(new \Gumstix\GeographyBundle\Service\AddressFormatter());
    }

    /**
     * Gets the private 'Gumstix\SSOBundle\Twig\SSOExtension' shared service.
     *
     * @return \Gumstix\SSOBundle\Twig\SSOExtension
     */
    protected function getSSOExtensionService()
    {
        return $this->services['Gumstix\\SSOBundle\\Twig\\SSOExtension'] = new \Gumstix\SSOBundle\Twig\SSOExtension(${($_ = isset($this->services['gumstix_sso.router']) ? $this->services['gumstix_sso.router'] : ($this->services['gumstix_sso.router'] = new \Gumstix\SSOBundle\Service\SSORouter('http://accounts.mystix.com/'))) && false ?: '_'}, ${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : ($this->services['request_stack'] = new \Symfony\Component\HttpFoundation\RequestStack())) && false ?: '_'});
    }

    /**
     * Gets the private 'Rialto\Allocation\EstimatedArrivalDate\EstimatedArrivalDateGenerator' shared autowired service.
     *
     * @return \Rialto\Allocation\EstimatedArrivalDate\EstimatedArrivalDateGenerator
     */
    protected function getEstimatedArrivalDateGeneratorService()
    {
        return $this->services['Rialto\\Allocation\\EstimatedArrivalDate\\EstimatedArrivalDateGenerator'] = new \Rialto\Allocation\EstimatedArrivalDate\EstimatedArrivalDateGenerator(${($_ = isset($this->services['Rialto\\Purchasing\\Producer\\CommitmentDateEstimator\\StockProducerCommitmentDateEstimator']) ? $this->services['Rialto\\Purchasing\\Producer\\CommitmentDateEstimator\\StockProducerCommitmentDateEstimator'] : $this->getStockProducerCommitmentDateEstimatorService()) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Shipping\\Method\\ShippingTimeEstimator\\ShippingTimeEstimatorInterface']) ? $this->services['Rialto\\Shipping\\Method\\ShippingTimeEstimator\\ShippingTimeEstimatorInterface'] : ($this->services['Rialto\\Shipping\\Method\\ShippingTimeEstimator\\ShippingTimeEstimatorInterface'] = new \Rialto\Shipping\Method\ShippingTimeEstimator\ShippingTimeEstimator())) && false ?: '_'});
    }

    /**
     * Gets the private 'Rialto\Cms\CmsLoader' shared autowired service.
     *
     * @return \Rialto\Cms\CmsLoader
     */
    protected function getCmsLoaderService()
    {
        return $this->services['Rialto\\Cms\\CmsLoader'] = new \Rialto\Cms\CmsLoader(${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'});
    }

    /**
     * Gets the private 'Rialto\Madison\Version\VersionChangeCache' shared autowired service.
     *
     * @return \Rialto\Madison\Version\VersionChangeCache
     */
    protected function getVersionChangeCacheService()
    {
        return $this->services['Rialto\\Madison\\Version\\VersionChangeCache'] = new \Rialto\Madison\Version\VersionChangeCache();
    }

    /**
     * Gets the private 'Rialto\Manufacturing\Task\ProductionTaskRefreshListener' shared autowired service.
     *
     * @return \Rialto\Manufacturing\Task\ProductionTaskRefreshListener
     */
    protected function getProductionTaskRefreshListenerService()
    {
        return $this->services['Rialto\\Manufacturing\\Task\\ProductionTaskRefreshListener'] = new \Rialto\Manufacturing\Task\ProductionTaskRefreshListener(${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->getDebug_EventDispatcherService()) && false ?: '_'});
    }

    /**
     * Gets the private 'Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository' autowired service.
     *
     * @return \Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository
     */
    protected function getPurchasingDataRepositoryService()
    {
        return ${($_ = isset($this->services['doctrine']) ? $this->services['doctrine'] : $this->getDoctrineService()) && false ?: '_'}->getRepository('Rialto\\Purchasing\\Catalog\\PurchasingData');
    }

    /**
     * Gets the private 'Rialto\Security\Nda\NdaFormListener' shared autowired service.
     *
     * @return \Rialto\Security\Nda\NdaFormListener
     */
    protected function getNdaFormListenerService()
    {
        return $this->services['Rialto\\Security\\Nda\\NdaFormListener'] = new \Rialto\Security\Nda\NdaFormListener(${($_ = isset($this->services['Rialto\\Security\\User\\UserManager']) ? $this->services['Rialto\\Security\\User\\UserManager'] : $this->getUserManagerService()) && false ?: '_'}, ${($_ = isset($this->services['Symfony\\Component\\Routing\\RouterInterface']) ? $this->services['Symfony\\Component\\Routing\\RouterInterface'] : $this->getRouterInterfaceService()) && false ?: '_'});
    }

    /**
     * Gets the private 'Rialto\Security\User\UserManager' shared autowired service.
     *
     * @return \Rialto\Security\User\SymfonyUserManager
     */
    protected function getUserManagerService()
    {
        return $this->services['Rialto\\Security\\User\\UserManager'] = new \Rialto\Security\User\SymfonyUserManager(${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : ($this->services['security.token_storage'] = new \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage())) && false ?: '_'}, ${($_ = isset($this->services['security.authorization_checker']) ? $this->services['security.authorization_checker'] : $this->getSecurity_AuthorizationCheckerService()) && false ?: '_'});
    }

    /**
     * Gets the private 'annotation_reader' shared service.
     *
     * @return \Doctrine\Common\Annotations\CachedReader
     */
    protected function getAnnotationReaderService()
    {
        return $this->services['annotation_reader'] = new \Doctrine\Common\Annotations\CachedReader(${($_ = isset($this->services['annotations.reader']) ? $this->services['annotations.reader'] : $this->getAnnotations_ReaderService()) && false ?: '_'}, ${($_ = isset($this->services['annotations.cache']) ? $this->services['annotations.cache'] : $this->load('getAnnotations_CacheService.php')) && false ?: '_'}, true);
    }

    /**
     * Gets the private 'annotations.reader' shared service.
     *
     * @return \Doctrine\Common\Annotations\AnnotationReader
     */
    protected function getAnnotations_ReaderService()
    {
        $this->services['annotations.reader'] = $instance = new \Doctrine\Common\Annotations\AnnotationReader();

        $a = new \Doctrine\Common\Annotations\AnnotationRegistry();
        $a->registerUniqueLoader('class_exists');

        $instance->addGlobalIgnoredName('required', $a);

        return $instance;
    }

    /**
     * Gets the private 'assets._version__default' shared service.
     *
     * @return \Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy
     */
    protected function getAssets_VersionDefaultService()
    {
        return $this->services['assets._version__default'] = new \Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy('2a685dc7e', '%s?%s');
    }

    /**
     * Gets the private 'assets.context' shared service.
     *
     * @return \Symfony\Component\Asset\Context\RequestStackContext
     */
    protected function getAssets_ContextService()
    {
        return $this->services['assets.context'] = new \Symfony\Component\Asset\Context\RequestStackContext(${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : ($this->services['request_stack'] = new \Symfony\Component\HttpFoundation\RequestStack())) && false ?: '_'}, '', false);
    }

    /**
     * Gets the private 'assets.packages' shared service.
     *
     * @return \Symfony\Component\Asset\Packages
     */
    protected function getAssets_PackagesService()
    {
        return $this->services['assets.packages'] = new \Symfony\Component\Asset\Packages(new \Symfony\Component\Asset\PathPackage('', ${($_ = isset($this->services['assets._version__default']) ? $this->services['assets._version__default'] : $this->getAssets_VersionDefaultService()) && false ?: '_'}, ${($_ = isset($this->services['assets.context']) ? $this->services['assets.context'] : $this->getAssets_ContextService()) && false ?: '_'}), []);
    }

    /**
     * Gets the private 'config_cache_factory' shared service.
     *
     * @return \Symfony\Component\Config\ResourceCheckerConfigCacheFactory
     */
    protected function getConfigCacheFactoryService()
    {
        return $this->services['config_cache_factory'] = new \Symfony\Component\Config\ResourceCheckerConfigCacheFactory(new RewindableGenerator(function () {
            yield 0 => ${($_ = isset($this->services['dependency_injection.config.container_parameters_resource_checker']) ? $this->services['dependency_injection.config.container_parameters_resource_checker'] : ($this->services['dependency_injection.config.container_parameters_resource_checker'] = new \Symfony\Component\DependencyInjection\Config\ContainerParametersResourceChecker($this))) && false ?: '_'};
            yield 1 => ${($_ = isset($this->services['config.resource.self_checking_resource_checker']) ? $this->services['config.resource.self_checking_resource_checker'] : ($this->services['config.resource.self_checking_resource_checker'] = new \Symfony\Component\Config\Resource\SelfCheckingResourceChecker())) && false ?: '_'};
        }, 2));
    }

    /**
     * Gets the private 'controller_name_converter' shared service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser
     */
    protected function getControllerNameConverterService()
    {
        return $this->services['controller_name_converter'] = new \Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser(${($_ = isset($this->services['kernel']) ? $this->services['kernel'] : $this->get('kernel', 1)) && false ?: '_'});
    }

    /**
     * Gets the private 'debug.argument_resolver' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Controller\TraceableArgumentResolver
     */
    protected function getDebug_ArgumentResolverService()
    {
        return $this->services['debug.argument_resolver'] = new \Symfony\Component\HttpKernel\Controller\TraceableArgumentResolver(new \Symfony\Component\HttpKernel\Controller\ArgumentResolver(new \Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactory(), new RewindableGenerator(function () {
            yield 0 => ${($_ = isset($this->services['argument_resolver.request_attribute']) ? $this->services['argument_resolver.request_attribute'] : ($this->services['argument_resolver.request_attribute'] = new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestAttributeValueResolver())) && false ?: '_'};
            yield 1 => ${($_ = isset($this->services['argument_resolver.request']) ? $this->services['argument_resolver.request'] : ($this->services['argument_resolver.request'] = new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestValueResolver())) && false ?: '_'};
            yield 2 => ${($_ = isset($this->services['argument_resolver.session']) ? $this->services['argument_resolver.session'] : ($this->services['argument_resolver.session'] = new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\SessionValueResolver())) && false ?: '_'};
            yield 3 => ${($_ = isset($this->services['security.user_value_resolver']) ? $this->services['security.user_value_resolver'] : $this->load('getSecurity_UserValueResolverService.php')) && false ?: '_'};
            yield 4 => ${($_ = isset($this->services['argument_resolver.service']) ? $this->services['argument_resolver.service'] : $this->load('getArgumentResolver_ServiceService.php')) && false ?: '_'};
            yield 5 => ${($_ = isset($this->services['argument_resolver.default']) ? $this->services['argument_resolver.default'] : ($this->services['argument_resolver.default'] = new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\DefaultValueResolver())) && false ?: '_'};
            yield 6 => ${($_ = isset($this->services['argument_resolver.variadic']) ? $this->services['argument_resolver.variadic'] : ($this->services['argument_resolver.variadic'] = new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\VariadicValueResolver())) && false ?: '_'};
        }, 7)), ${($_ = isset($this->services['debug.stopwatch']) ? $this->services['debug.stopwatch'] : ($this->services['debug.stopwatch'] = new \Symfony\Component\Stopwatch\Stopwatch(true))) && false ?: '_'});
    }

    /**
     * Gets the private 'debug.controller_resolver' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Controller\TraceableControllerResolver
     */
    protected function getDebug_ControllerResolverService()
    {
        return $this->services['debug.controller_resolver'] = new \Symfony\Component\HttpKernel\Controller\TraceableControllerResolver(new \Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver($this, ${($_ = isset($this->services['controller_name_converter']) ? $this->services['controller_name_converter'] : ($this->services['controller_name_converter'] = new \Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser(${($_ = isset($this->services['kernel']) ? $this->services['kernel'] : $this->get('kernel', 1)) && false ?: '_'}))) && false ?: '_'}, ${($_ = isset($this->services['monolog.logger.request']) ? $this->services['monolog.logger.request'] : $this->getMonolog_Logger_RequestService()) && false ?: '_'}), ${($_ = isset($this->services['debug.stopwatch']) ? $this->services['debug.stopwatch'] : ($this->services['debug.stopwatch'] = new \Symfony\Component\Stopwatch\Stopwatch(true))) && false ?: '_'}, ${($_ = isset($this->services['debug.argument_resolver']) ? $this->services['debug.argument_resolver'] : $this->getDebug_ArgumentResolverService()) && false ?: '_'});
    }

    /**
     * Gets the private 'debug.debug_handlers_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\DebugHandlersListener
     */
    protected function getDebug_DebugHandlersListenerService()
    {
        return $this->services['debug.debug_handlers_listener'] = new \Symfony\Component\HttpKernel\EventListener\DebugHandlersListener(NULL, ${($_ = isset($this->services['monolog.logger.php']) ? $this->services['monolog.logger.php'] : $this->getMonolog_Logger_PhpService()) && false ?: '_'}, -1, -1, true, ${($_ = isset($this->services['debug.file_link_formatter']) ? $this->services['debug.file_link_formatter'] : $this->getDebug_FileLinkFormatterService()) && false ?: '_'}, true);
    }

    /**
     * Gets the private 'debug.event_dispatcher' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher
     */
    protected function getDebug_EventDispatcherService()
    {
        $this->services['debug.event_dispatcher'] = $instance = new \Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher(new \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher($this), ${($_ = isset($this->services['debug.stopwatch']) ? $this->services['debug.stopwatch'] : ($this->services['debug.stopwatch'] = new \Symfony\Component\Stopwatch\Stopwatch(true))) && false ?: '_'}, ${($_ = isset($this->services['monolog.logger.event']) ? $this->services['monolog.logger.event'] : $this->getMonolog_Logger_EventService()) && false ?: '_'});

        $instance->addListener('kernel.terminate', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Allocation\\Allocation\\EmptyAllocationRemover']) ? $this->services['Rialto\\Allocation\\Allocation\\EmptyAllocationRemover'] : $this->load('getEmptyAllocationRemoverService.php')) && false ?: '_'};
        }, 1 => 'onKernelTerminate'], 0);
        $instance->addListener('kernel.exception', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Cms\\ExceptionHandler']) ? $this->services['Rialto\\Cms\\ExceptionHandler'] : $this->load('getExceptionHandlerService.php')) && false ?: '_'};
        }, 1 => 'onException'], 0);
        $instance->addListener('kernel.request', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Filetype\\Postscript\\FontFilesystem']) ? $this->services['Rialto\\Filetype\\Postscript\\FontFilesystem'] : ($this->services['Rialto\\Filetype\\Postscript\\FontFilesystem'] = new \Rialto\Filetype\Postscript\FontFilesystem(($this->targetDirs[4].'/fonts')))) && false ?: '_'};
        }, 1 => 'initPostscriptFonts'], 0);
        $instance->addListener('rialto_sales.capture_payment', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Shopify\\Order\\PaymentProcessor']) ? $this->services['Rialto\\Shopify\\Order\\PaymentProcessor'] : $this->load('getPaymentProcessor3Service.php')) && false ?: '_'};
        }, 1 => 'capturePayment'], 0);
        $instance->addListener('rialto_sales.order_invoice', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Shopify\\Order\\FulfillmentListener']) ? $this->services['Rialto\\Shopify\\Order\\FulfillmentListener'] : $this->load('getFulfillmentListenerService.php')) && false ?: '_'};
        }, 1 => 'onOrderInvoice'], 0);
        $instance->addListener('rialto_sales.order_closed', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Shopify\\Order\\OrderClosedListener']) ? $this->services['Rialto\\Shopify\\Order\\OrderClosedListener'] : $this->load('getOrderClosedListener2Service.php')) && false ?: '_'};
        }, 1 => 'onOrderClosed'], 0);
        $instance->addListener('flow.previous_step_invalid', [0 => function () {
            return ${($_ = isset($this->services['craue.form.flow.event_listener.previous_step_invalid']) ? $this->services['craue.form.flow.event_listener.previous_step_invalid'] : $this->load('getCraue_Form_Flow_EventListener_PreviousStepInvalidService.php')) && false ?: '_'};
        }, 1 => 'onPreviousStepInvalid'], 0);
        $instance->addListener('flow.flow_expired', [0 => function () {
            return ${($_ = isset($this->services['craue.form.flow.event_listener.flow_expired']) ? $this->services['craue.form.flow.event_listener.flow_expired'] : $this->load('getCraue_Form_Flow_EventListener_FlowExpiredService.php')) && false ?: '_'};
        }, 1 => 'onFlowExpired'], 0);
        $instance->addListener('kernel.controller', [0 => function () {
            return ${($_ = isset($this->services['easyadmin.listener.controller']) ? $this->services['easyadmin.listener.controller'] : $this->getEasyadmin_Listener_ControllerService()) && false ?: '_'};
        }, 1 => 'onKernelController'], 0);
        $instance->addListener('easy_admin.post_initialize', [0 => function () {
            return ${($_ = isset($this->services['easyadmin.listener.request_post_initialize']) ? $this->services['easyadmin.listener.request_post_initialize'] : $this->load('getEasyadmin_Listener_RequestPostInitializeService.php')) && false ?: '_'};
        }, 1 => 'initializeRequest'], 0);
        $instance->addListener('kernel.request', [0 => function () {
            return ${($_ = isset($this->services['fos_rest.body_listener']) ? $this->services['fos_rest.body_listener'] : $this->getFosRest_BodyListenerService()) && false ?: '_'};
        }, 1 => 'onKernelRequest'], 10);
        $instance->addListener('kernel.request', [0 => function () {
            return ${($_ = isset($this->services['fos_rest.format_listener']) ? $this->services['fos_rest.format_listener'] : $this->getFosRest_FormatListenerService()) && false ?: '_'};
        }, 1 => 'onKernelRequest'], 34);
        $instance->addListener('kernel.response', [0 => function () {
            return ${($_ = isset($this->services['nelmio_security.external_redirect_listener']) ? $this->services['nelmio_security.external_redirect_listener'] : $this->getNelmioSecurity_ExternalRedirectListenerService()) && false ?: '_'};
        }, 1 => 'onKernelResponse'], 0);
        $instance->addListener('kernel.request', [0 => function () {
            return ${($_ = isset($this->services['nelmio_cors.cors_listener']) ? $this->services['nelmio_cors.cors_listener'] : $this->getNelmioCors_CorsListenerService()) && false ?: '_'};
        }, 1 => 'onKernelRequest'], 250);
        $instance->addListener('kernel.exception', [0 => function () {
            return ${($_ = isset($this->services['webpack_encore.exception_listener']) ? $this->services['webpack_encore.exception_listener'] : $this->load('getWebpackEncore_ExceptionListenerService.php')) && false ?: '_'};
        }, 1 => 'onKernelException'], 0);
        $instance->addListener('rialto_purchasing.goods_received', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Allocation\\Dispatch\\DispatchInstructionSubscriber']) ? $this->services['Rialto\\Allocation\\Dispatch\\DispatchInstructionSubscriber'] : $this->load('getDispatchInstructionSubscriberService.php')) && false ?: '_'};
        }, 1 => 'onGoodsReceived'], 0);
        $instance->addListener('rialto_allocation.consumer_change', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Allocation\\Consumer\\StockConsumerListener']) ? $this->services['Rialto\\Allocation\\Consumer\\StockConsumerListener'] : ($this->services['Rialto\\Allocation\\Consumer\\StockConsumerListener'] = new \Rialto\Allocation\Consumer\StockConsumerListener())) && false ?: '_'};
        }, 1 => 'onStockConsumerChange'], 0);
        $instance->addListener('rialto_stock.creation', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Allocation\\Allocation\\AllocationTransferListener']) ? $this->services['Rialto\\Allocation\\Allocation\\AllocationTransferListener'] : $this->load('getAllocationTransferListenerService.php')) && false ?: '_'};
        }, 1 => 'onStockCreation'], 0);
        $instance->addListener('rialto_manufacturing.add_production_tasks', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Catalina\\ProductionTaskListener']) ? $this->services['Rialto\\Catalina\\ProductionTaskListener'] : $this->load('getProductionTaskListenerService.php')) && false ?: '_'};
        }, 1 => 'addProductionTasks'], 0);
        $instance->addListener('kernel.exception', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Database\\Orm\\LockExceptionHandler']) ? $this->services['Rialto\\Database\\Orm\\LockExceptionHandler'] : $this->load('getLockExceptionHandlerService.php')) && false ?: '_'};
        }, 1 => 'onException'], 0);
        $instance->addListener('rialto_manufacturing.new_bom', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Geppetto\\StandardCostListener']) ? $this->services['Rialto\\Geppetto\\StandardCostListener'] : $this->load('getStandardCostListenerService.php')) && false ?: '_'};
        }, 1 => 'setModuleStandardCost'], -10);
        $instance->addListener('kernel.terminate', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Madison\\Version\\VersionChangeNotifier']) ? $this->services['Rialto\\Madison\\Version\\VersionChangeNotifier'] : $this->load('getVersionChangeNotifierService.php')) && false ?: '_'};
        }, 1 => 'notifyMadison'], 0);
        $instance->addListener('rialto_sales.capture_payment', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Magento2\\Order\\PaymentProcessor']) ? $this->services['Rialto\\Magento2\\Order\\PaymentProcessor'] : $this->load('getPaymentProcessorService.php')) && false ?: '_'};
        }, 1 => 'createInvoiceAndCapturePayment'], 0);
        $instance->addListener('rialto_sales.order_invoice', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Magento2\\Order\\ShipmentListener']) ? $this->services['Rialto\\Magento2\\Order\\ShipmentListener'] : $this->load('getShipmentListenerService.php')) && false ?: '_'};
        }, 1 => 'createShipment'], 0);
        $instance->addListener('rialto_sales.order_closed', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Magento2\\Order\\OrderClosedListener']) ? $this->services['Rialto\\Magento2\\Order\\OrderClosedListener'] : $this->load('getOrderClosedListenerService.php')) && false ?: '_'};
        }, 1 => 'closeOrder'], 0);
        $instance->addListener('rialto_stock.level_update', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Magento2\\Stock\\StockUpdateListener']) ? $this->services['Rialto\\Magento2\\Stock\\StockUpdateListener'] : $this->load('getStockUpdateListenerService.php')) && false ?: '_'};
        }, 1 => 'updateStockLevel'], 0);
        $instance->addListener('magento2.suspected_fraud', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Magento2\\Order\\SuspectedFraudListener']) ? $this->services['Rialto\\Magento2\\Order\\SuspectedFraudListener'] : $this->load('getSuspectedFraudListenerService.php')) && false ?: '_'};
        }, 1 => 'notifyOfSuspectedFraud'], 0);
        $instance->addListener('rialto_manufacturing.new_bom', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Manufacturing\\Bom\\Bag\\AddBagToBomListener']) ? $this->services['Rialto\\Manufacturing\\Bom\\Bag\\AddBagToBomListener'] : $this->load('getAddBagToBomListenerService.php')) && false ?: '_'};
        }, 1 => 'addBagIfNeeded'], 0);
        $instance->addListener('rialto_stock.transfer_sent', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Manufacturing\\Kit\\Reminder\\EmailScheduler']) ? $this->services['Rialto\\Manufacturing\\Kit\\Reminder\\EmailScheduler'] : $this->load('getEmailSchedulerService.php')) && false ?: '_'};
        }, 1 => 'schedulePesterEmailIfNeeded'], 0);
        $instance->addListener('rialto_stock.transfer_sent', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Manufacturing\\WorkOrder\\TransferEventListener']) ? $this->services['Rialto\\Manufacturing\\WorkOrder\\TransferEventListener'] : $this->load('getTransferEventListenerService.php')) && false ?: '_'};
        }, 1 => 'updateOrders'], 0);
        $instance->addListener('rialto_stock.transfer_receipt', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Manufacturing\\WorkOrder\\TransferEventListener']) ? $this->services['Rialto\\Manufacturing\\WorkOrder\\TransferEventListener'] : $this->load('getTransferEventListenerService.php')) && false ?: '_'};
        }, 1 => 'updateOrders'], 0);
        $instance->addListener('rialto_stock.missing_item_resolved', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Manufacturing\\WorkOrder\\TransferEventListener']) ? $this->services['Rialto\\Manufacturing\\WorkOrder\\TransferEventListener'] : $this->load('getTransferEventListenerService.php')) && false ?: '_'};
        }, 1 => 'updateOrders'], 0);
        $instance->addListener('Rialto\\Purchasing\\Order\\Event\\PurchaseOrderSent', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Manufacturing\\PurchaseOrder\\PartsOrderSentListener']) ? $this->services['Rialto\\Manufacturing\\PurchaseOrder\\PartsOrderSentListener'] : $this->load('getPartsOrderSentListenerService.php')) && false ?: '_'};
        }, 1 => 'updateDependentOrders'], 0);
        $instance->addListener('kernel.terminate', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Manufacturing\\Task\\ProductionTaskRefreshListener']) ? $this->services['Rialto\\Manufacturing\\Task\\ProductionTaskRefreshListener'] : $this->getProductionTaskRefreshListenerService()) && false ?: '_'};
        }, 1 => 'refreshTasksAndJobs'], 0);
        $instance->addListener('console.terminate', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Manufacturing\\Task\\ProductionTaskRefreshListener']) ? $this->services['Rialto\\Manufacturing\\Task\\ProductionTaskRefreshListener'] : $this->getProductionTaskRefreshListenerService()) && false ?: '_'};
        }, 1 => 'refreshTasksAndJobs'], 0);
        $instance->addListener('Rialto\\Purchasing\\Order\\Event\\PurchaseOrderRejected', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Purchasing\\EmailEventSubscriber']) ? $this->services['Rialto\\Purchasing\\EmailEventSubscriber'] : $this->load('getEmailEventSubscriberService.php')) && false ?: '_'};
        }, 1 => 'onOrderRejected'], 0);
        $instance->addListener('rialto_purchasing.goods_received', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Purchasing\\EmailEventSubscriber']) ? $this->services['Rialto\\Purchasing\\EmailEventSubscriber'] : $this->load('getEmailEventSubscriberService.php')) && false ?: '_'};
        }, 1 => 'onGoodsReceived'], 0);
        $instance->addListener('rialto_sales.capture_payment', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Payment\\PaymentProcessor']) ? $this->services['Rialto\\Payment\\PaymentProcessor'] : $this->load('getPaymentProcessor2Service.php')) && false ?: '_'};
        }, 1 => 'capturePayment'], -20);
        $instance->addListener('rialto_sales.order_closed', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Payment\\PaymentProcessor']) ? $this->services['Rialto\\Payment\\PaymentProcessor'] : $this->load('getPaymentProcessor2Service.php')) && false ?: '_'};
        }, 1 => 'voidUncapturedTransactions'], -20);
        $instance->addListener('Rialto\\PcbNg\\Event\\PendingReview', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer']) ? $this->services['Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer'] : $this->load('getPcbNgNotificationEmailerService.php')) && false ?: '_'};
        }, 1 => 'onPendingReview'], 0);
        $instance->addListener('Rialto\\PcbNg\\Event\\OnHold', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer']) ? $this->services['Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer'] : $this->load('getPcbNgNotificationEmailerService.php')) && false ?: '_'};
        }, 1 => 'onHold'], 0);
        $instance->addListener('Rialto\\PcbNg\\Event\\Cancelled', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer']) ? $this->services['Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer'] : $this->load('getPcbNgNotificationEmailerService.php')) && false ?: '_'};
        }, 1 => 'onCancelled'], 0);
        $instance->addListener('Rialto\\PcbNg\\Event\\QueuedForFabrication', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer']) ? $this->services['Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer'] : $this->load('getPcbNgNotificationEmailerService.php')) && false ?: '_'};
        }, 1 => 'onQueuedForFabrication'], 0);
        $instance->addListener('Rialto\\PcbNg\\Event\\QueuedForManufacturing', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer']) ? $this->services['Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer'] : $this->load('getPcbNgNotificationEmailerService.php')) && false ?: '_'};
        }, 1 => 'onQueuedForManufacturing'], 0);
        $instance->addListener('Rialto\\PcbNg\\Event\\InFabrication', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer']) ? $this->services['Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer'] : $this->load('getPcbNgNotificationEmailerService.php')) && false ?: '_'};
        }, 1 => 'onInFabrication'], 0);
        $instance->addListener('Rialto\\PcbNg\\Event\\InManufacturing', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer']) ? $this->services['Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer'] : $this->load('getPcbNgNotificationEmailerService.php')) && false ?: '_'};
        }, 1 => 'onInManufacturing'], 0);
        $instance->addListener('Rialto\\PcbNg\\Event\\Shipped', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer']) ? $this->services['Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer'] : $this->load('getPcbNgNotificationEmailerService.php')) && false ?: '_'};
        }, 1 => 'onShipped'], 0);
        $instance->addListener('Rialto\\PcbNg\\Event\\Refunded', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer']) ? $this->services['Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer'] : $this->load('getPcbNgNotificationEmailerService.php')) && false ?: '_'};
        }, 1 => 'onRefunded'], 0);
        $instance->addListener('Rialto\\PcbNg\\Event\\Error', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer']) ? $this->services['Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer'] : $this->load('getPcbNgNotificationEmailerService.php')) && false ?: '_'};
        }, 1 => 'onError'], 0);
        $instance->addListener('rialto.sales.sales_return_disposition', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Purchasing\\Order\\AutoSendReworkOrderSubscriber']) ? $this->services['Rialto\\Purchasing\\Order\\AutoSendReworkOrderSubscriber'] : $this->load('getAutoSendReworkOrderSubscriberService.php')) && false ?: '_'};
        }, 1 => 'onSalesReturnDisposition'], 0);
        $instance->addListener('rialto.form.handle_error', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Purchasing\\PurchasingErrorHandler']) ? $this->services['Rialto\\Purchasing\\PurchasingErrorHandler'] : $this->load('getPurchasingErrorHandlerService.php')) && false ?: '_'};
        }, 1 => 'onFormError'], 0);
        $instance->addListener('rialto_purchasing.goods_received', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Purchasing\\Receiving\\GoodsReceivedLogger']) ? $this->services['Rialto\\Purchasing\\Receiving\\GoodsReceivedLogger'] : $this->load('getGoodsReceivedLoggerService.php')) && false ?: '_'};
        }, 1 => 'logGrn'], 0);
        $instance->addListener('rialto_purchasing.goods_received', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Purchasing\\Receiving\\Notify\\XmppEventSubscriber']) ? $this->services['Rialto\\Purchasing\\Receiving\\Notify\\XmppEventSubscriber'] : $this->load('getXmppEventSubscriberService.php')) && false ?: '_'};
        }, 1 => 'sendXmppMessageIfNeeded'], 0);
        $instance->addListener('rialto_sales.allocation', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Sales\\Order\\Dates\\TargetShipDateListener']) ? $this->services['Rialto\\Sales\\Order\\Dates\\TargetShipDateListener'] : $this->load('getTargetShipDateListenerService.php')) && false ?: '_'};
        }, 1 => 'onOrderAllocated'], -100);
        $instance->addListener('rialto.sales.credit', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Sales\\Order\\Dates\\TargetShipDateListener']) ? $this->services['Rialto\\Sales\\Order\\Dates\\TargetShipDateListener'] : $this->load('getTargetShipDateListenerService.php')) && false ?: '_'};
        }, 1 => 'onCustomerCredit'], -100);
        $instance->addListener('rialto_sales.allocation', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Sales\\Order\\Email\\OrderEmailListener']) ? $this->services['Rialto\\Sales\\Order\\Email\\OrderEmailListener'] : $this->load('getOrderEmailListenerService.php')) && false ?: '_'};
        }, 1 => 'notifyIfUnableToAllocate'], 0);
        $instance->addListener('rialto_sales.order_authorized', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Sales\\Order\\Email\\OrderEmailListener']) ? $this->services['Rialto\\Sales\\Order\\Email\\OrderEmailListener'] : $this->load('getOrderEmailListenerService.php')) && false ?: '_'};
        }, 1 => 'notifyWhenCustomerHasPaid'], 0);
        $instance->addListener('rialto_sales.allocation', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Sales\\Shipping\\ApproveToShipEventListener']) ? $this->services['Rialto\\Sales\\Shipping\\ApproveToShipEventListener'] : $this->load('getApproveToShipEventListenerService.php')) && false ?: '_'};
        }, 1 => 'approveToShipIfReady'], 0);
        $instance->addListener('rialto.sales.credit', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Sales\\Order\\OrderUpdateListener']) ? $this->services['Rialto\\Sales\\Order\\OrderUpdateListener'] : ($this->services['Rialto\\Sales\\Order\\OrderUpdateListener'] = new \Rialto\Sales\Order\OrderUpdateListener())) && false ?: '_'};
        }, 1 => 'convertToOrder'], 10);
        $instance->addListener('rialto_sales.order_authorized', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Sales\\Order\\Allocation\\AllocationEventListener']) ? $this->services['Rialto\\Sales\\Order\\Allocation\\AllocationEventListener'] : $this->load('getAllocationEventListenerService.php')) && false ?: '_'};
        }, 1 => 'allocate'], 0);
        $instance->addListener('rialto_sales.order_authorized', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Sales\\Order\\SoftwareInvoicer']) ? $this->services['Rialto\\Sales\\Order\\SoftwareInvoicer'] : $this->load('getSoftwareInvoicerService.php')) && false ?: '_'};
        }, 1 => 'handleOrderAuthorized'], 0);
        $instance->addListener('rialto.sales.credit', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Sales\\EmailEventListener']) ? $this->services['Rialto\\Sales\\EmailEventListener'] : $this->load('getEmailEventListenerService.php')) && false ?: '_'};
        }, 1 => 'notifyCustomerOfReceipt'], -20);
        $instance->addListener('rialto_sales.order_invoice', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Sales\\EmailEventListener']) ? $this->services['Rialto\\Sales\\EmailEventListener'] : $this->load('getEmailEventListenerService.php')) && false ?: '_'};
        }, 1 => 'notifyCustomerOfShipment'], -20);
        $instance->addListener('rialto.sales.sales_return_disposition', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Sales\\EmailEventListener']) ? $this->services['Rialto\\Sales\\EmailEventListener'] : $this->load('getEmailEventListenerService.php')) && false ?: '_'};
        }, 1 => 'notifyAuthorizerOfDisposition'], 0);
        $instance->addListener('rialto_sales.order_invoice', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Sales\\DocumentEventListener']) ? $this->services['Rialto\\Sales\\DocumentEventListener'] : $this->load('getDocumentEventListenerService.php')) && false ?: '_'};
        }, 1 => 'onOrderInvoice'], -5);
        $instance->addListener('rialto_sales.approved_to_ship', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Sales\\DocumentEventListener']) ? $this->services['Rialto\\Sales\\DocumentEventListener'] : $this->load('getDocumentEventListenerService.php')) && false ?: '_'};
        }, 1 => 'onApprovedToShip'], 0);
        $instance->addListener('security.interactive_login', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Security\\User\\LastLoginUpdater']) ? $this->services['Rialto\\Security\\User\\LastLoginUpdater'] : $this->load('getLastLoginUpdaterService.php')) && false ?: '_'};
        }, 1 => 'updateLoginDate'], 0);
        $instance->addListener('kernel.exception', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Security\\Firewall\\UsernameNotFoundExceptionHandler']) ? $this->services['Rialto\\Security\\Firewall\\UsernameNotFoundExceptionHandler'] : ($this->services['Rialto\\Security\\Firewall\\UsernameNotFoundExceptionHandler'] = new \Rialto\Security\Firewall\UsernameNotFoundExceptionHandler())) && false ?: '_'};
        }, 1 => 'onException'], 0);
        $instance->addListener('kernel.request', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Security\\Nda\\NdaFormListener']) ? $this->services['Rialto\\Security\\Nda\\NdaFormListener'] : $this->getNdaFormListenerService()) && false ?: '_'};
        }, 1 => 'onKernelRequest'], 0);
        $instance->addListener('rialto_stock.transfer_sent', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Stock\\Bin\\Label\\BinLabelListener']) ? $this->services['Rialto\\Stock\\Bin\\Label\\BinLabelListener'] : $this->load('getBinLabelListenerService.php')) && false ?: '_'};
        }, 1 => 'printLabels'], -100);
        $instance->addListener('rialto_stock.creation', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Stock\\Bin\\Label\\BinLabelListener']) ? $this->services['Rialto\\Stock\\Bin\\Label\\BinLabelListener'] : $this->load('getBinLabelListenerService.php')) && false ?: '_'};
        }, 1 => 'printLabels'], -100);
        $instance->addListener('rialto_stock.adjustment', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Stock\\Bin\\Label\\BinLabelListener']) ? $this->services['Rialto\\Stock\\Bin\\Label\\BinLabelListener'] : $this->load('getBinLabelListenerService.php')) && false ?: '_'};
        }, 1 => 'printLabels'], -100);
        $instance->addListener('rialto_stock.bin_split', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Stock\\Bin\\Label\\BinLabelListener']) ? $this->services['Rialto\\Stock\\Bin\\Label\\BinLabelListener'] : $this->load('getBinLabelListenerService.php')) && false ?: '_'};
        }, 1 => 'printLabels'], -100);
        $instance->addListener('rialto_stock.bin_change', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Stock\\Bin\\StockBinUpdateListener']) ? $this->services['Rialto\\Stock\\Bin\\StockBinUpdateListener'] : $this->load('getStockBinUpdateListenerService.php')) && false ?: '_'};
        }, 1 => 'stockBinChangeUpdateStockLevel'], 0);
        $instance->addListener('rialto_stock.bin_split', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Stock\\Shelf\\Position\\AssignmentListener']) ? $this->services['Rialto\\Stock\\Shelf\\Position\\AssignmentListener'] : $this->load('getAssignmentListenerService.php')) && false ?: '_'};
        }, 1 => 'onBinMove'], 100);
        $instance->addListener('rialto_stock.missing_item_resolved', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Stock\\Shelf\\Position\\AssignmentListener']) ? $this->services['Rialto\\Stock\\Shelf\\Position\\AssignmentListener'] : $this->load('getAssignmentListenerService.php')) && false ?: '_'};
        }, 1 => 'onBinMove'], 100);
        $instance->addListener('rialto_stock.creation', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Stock\\Shelf\\Position\\AssignmentListener']) ? $this->services['Rialto\\Stock\\Shelf\\Position\\AssignmentListener'] : $this->load('getAssignmentListenerService.php')) && false ?: '_'};
        }, 1 => 'onBinMove'], 100);
        $instance->addListener('rialto_stock.transfer_receipt', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Stock\\Shelf\\Position\\AssignmentListener']) ? $this->services['Rialto\\Stock\\Shelf\\Position\\AssignmentListener'] : $this->load('getAssignmentListenerService.php')) && false ?: '_'};
        }, 1 => 'onBinMove'], 100);
        $instance->addListener('rialto_stock.adjustment', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Stock\\Shelf\\Position\\AssignmentListener']) ? $this->services['Rialto\\Stock\\Shelf\\Position\\AssignmentListener'] : $this->load('getAssignmentListenerService.php')) && false ?: '_'};
        }, 1 => 'onBinMove'], 100);
        $instance->addListener('Rialto\\Stock\\Bin\\Event\\BinQuantityChanged', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Stock\\Transfer\\BinEventListener']) ? $this->services['Rialto\\Stock\\Transfer\\BinEventListener'] : $this->load('getBinEventListenerService.php')) && false ?: '_'};
        }, 1 => 'onBinQtyChanged'], 0);
        $instance->addListener('rialto_stock.transfer_receipt', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Stock\\EmailEventListener']) ? $this->services['Rialto\\Stock\\EmailEventListener'] : $this->load('getEmailEventListener2Service.php')) && false ?: '_'};
        }, 1 => 'emailTransferShortage'], 0);
        $instance->addListener('rialto_stock.bin_split', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Stock\\EmailEventListener']) ? $this->services['Rialto\\Stock\\EmailEventListener'] : $this->load('getEmailEventListener2Service.php')) && false ?: '_'};
        }, 1 => 'requestBinSplit'], 0);
        $instance->addListener('rialto_supplier.additional_part', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Supplier\\Logger']) ? $this->services['Rialto\\Supplier\\Logger'] : $this->load('getLoggerService.php')) && false ?: '_'};
        }, 1 => 'additionalPart'], 0);
        $instance->addListener('Rialto\\Purchasing\\Order\\Event\\PurchaseOrderApproved', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Supplier\\Logger']) ? $this->services['Rialto\\Supplier\\Logger'] : $this->load('getLoggerService.php')) && false ?: '_'};
        }, 1 => 'approveOrder'], 0);
        $instance->addListener('Rialto\\Purchasing\\Order\\Event\\PurchaseOrderRejected', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Supplier\\Logger']) ? $this->services['Rialto\\Supplier\\Logger'] : $this->load('getLoggerService.php')) && false ?: '_'};
        }, 1 => 'rejectOrder'], 0);
        $instance->addListener('rialto_supplier.supplier_reference', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Supplier\\Logger']) ? $this->services['Rialto\\Supplier\\Logger'] : $this->load('getLoggerService.php')) && false ?: '_'};
        }, 1 => 'supplierReference'], 0);
        $instance->addListener('rialto_allocation.change', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Supplier\\Logger']) ? $this->services['Rialto\\Supplier\\Logger'] : $this->load('getLoggerService.php')) && false ?: '_'};
        }, 1 => 'allocationChange'], 0);
        $instance->addListener('rialto_stock.transfer_sent', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Supplier\\Logger']) ? $this->services['Rialto\\Supplier\\Logger'] : $this->load('getLoggerService.php')) && false ?: '_'};
        }, 1 => 'transferSent'], 0);
        $instance->addListener('rialto_stock.transfer_receipt', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Supplier\\Logger']) ? $this->services['Rialto\\Supplier\\Logger'] : $this->load('getLoggerService.php')) && false ?: '_'};
        }, 1 => 'transferReceipt'], 0);
        $instance->addListener('rialto_supplier.commitment_date', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Supplier\\Logger']) ? $this->services['Rialto\\Supplier\\Logger'] : $this->load('getLoggerService.php')) && false ?: '_'};
        }, 1 => 'commitmentDate'], 0);
        $instance->addListener('rialto_supplier.audit', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Supplier\\Logger']) ? $this->services['Rialto\\Supplier\\Logger'] : $this->load('getLoggerService.php')) && false ?: '_'};
        }, 1 => 'audit'], 0);
        $instance->addListener('rialto_manufacturing.work_order_issue', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Supplier\\Logger']) ? $this->services['Rialto\\Supplier\\Logger'] : $this->load('getLoggerService.php')) && false ?: '_'};
        }, 1 => 'workOrderIssue'], 0);
        $instance->addListener('rialto_supplier.additional_part', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Supplier\\Order\\Email\\EmailSubscriber']) ? $this->services['Rialto\\Supplier\\Order\\Email\\EmailSubscriber'] : $this->load('getEmailSubscriberService.php')) && false ?: '_'};
        }, 1 => 'requestAdditionalPart'], 0);
        $instance->addListener('rialto_sales.order_invoice', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Ups\\Shipping\\Label\\ShippingLabelListener']) ? $this->services['Rialto\\Ups\\Shipping\\Label\\ShippingLabelListener'] : $this->load('getShippingLabelListenerService.php')) && false ?: '_'};
        }, 1 => 'onOrderInvoice'], 0);
        $instance->addListener('rialto_stock.change_notice', [0 => function () {
            return ${($_ = isset($this->services['Rialto\\Wordpress\\ChangeNoticeListener']) ? $this->services['Rialto\\Wordpress\\ChangeNoticeListener'] : $this->load('getChangeNoticeListenerService.php')) && false ?: '_'};
        }, 1 => 'onChangeNotice'], 0);
        $instance->addListener('kernel.response', [0 => function () {
            return ${($_ = isset($this->services['response_listener']) ? $this->services['response_listener'] : ($this->services['response_listener'] = new \Symfony\Component\HttpKernel\EventListener\ResponseListener('UTF-8'))) && false ?: '_'};
        }, 1 => 'onKernelResponse'], 0);
        $instance->addListener('kernel.response', [0 => function () {
            return ${($_ = isset($this->services['streamed_response_listener']) ? $this->services['streamed_response_listener'] : ($this->services['streamed_response_listener'] = new \Symfony\Component\HttpKernel\EventListener\StreamedResponseListener())) && false ?: '_'};
        }, 1 => 'onKernelResponse'], -1024);
        $instance->addListener('kernel.request', [0 => function () {
            return ${($_ = isset($this->services['locale_listener']) ? $this->services['locale_listener'] : $this->getLocaleListenerService()) && false ?: '_'};
        }, 1 => 'onKernelRequest'], 16);
        $instance->addListener('kernel.finish_request', [0 => function () {
            return ${($_ = isset($this->services['locale_listener']) ? $this->services['locale_listener'] : $this->getLocaleListenerService()) && false ?: '_'};
        }, 1 => 'onKernelFinishRequest'], 0);
        $instance->addListener('kernel.request', [0 => function () {
            return ${($_ = isset($this->services['validate_request_listener']) ? $this->services['validate_request_listener'] : ($this->services['validate_request_listener'] = new \Symfony\Component\HttpKernel\EventListener\ValidateRequestListener())) && false ?: '_'};
        }, 1 => 'onKernelRequest'], 256);
        $instance->addListener('kernel.request', [0 => function () {
            return ${($_ = isset($this->services['resolve_controller_name_subscriber']) ? $this->services['resolve_controller_name_subscriber'] : $this->getResolveControllerNameSubscriberService()) && false ?: '_'};
        }, 1 => 'onKernelRequest'], 24);
        $instance->addListener('console.error', [0 => function () {
            return ${($_ = isset($this->services['console.error_listener']) ? $this->services['console.error_listener'] : $this->load('getConsole_ErrorListenerService.php')) && false ?: '_'};
        }, 1 => 'onConsoleError'], -128);
        $instance->addListener('console.terminate', [0 => function () {
            return ${($_ = isset($this->services['console.error_listener']) ? $this->services['console.error_listener'] : $this->load('getConsole_ErrorListenerService.php')) && false ?: '_'};
        }, 1 => 'onConsoleTerminate'], -128);
        $instance->addListener('kernel.request', [0 => function () {
            return ${($_ = isset($this->services['session_listener']) ? $this->services['session_listener'] : $this->getSessionListenerService()) && false ?: '_'};
        }, 1 => 'onKernelRequest'], 128);
        $instance->addListener('kernel.response', [0 => function () {
            return ${($_ = isset($this->services['session_listener']) ? $this->services['session_listener'] : $this->getSessionListenerService()) && false ?: '_'};
        }, 1 => 'onKernelResponse'], -1000);
        $instance->addListener('kernel.finish_request', [0 => function () {
            return ${($_ = isset($this->services['session_listener']) ? $this->services['session_listener'] : $this->getSessionListenerService()) && false ?: '_'};
        }, 1 => 'onFinishRequest'], 0);
        $instance->addListener('kernel.response', [0 => function () {
            return ${($_ = isset($this->services['session.save_listener']) ? $this->services['session.save_listener'] : ($this->services['session.save_listener'] = new \Symfony\Component\HttpKernel\EventListener\SaveSessionListener())) && false ?: '_'};
        }, 1 => 'onKernelResponse'], -1000);
        $instance->addListener('kernel.request', [0 => function () {
            return ${($_ = isset($this->services['translator_listener']) ? $this->services['translator_listener'] : $this->getTranslatorListenerService()) && false ?: '_'};
        }, 1 => 'onKernelRequest'], 10);
        $instance->addListener('kernel.finish_request', [0 => function () {
            return ${($_ = isset($this->services['translator_listener']) ? $this->services['translator_listener'] : $this->getTranslatorListenerService()) && false ?: '_'};
        }, 1 => 'onKernelFinishRequest'], 0);
        $instance->addListener('kernel.request', [0 => function () {
            return ${($_ = isset($this->services['debug.debug_handlers_listener']) ? $this->services['debug.debug_handlers_listener'] : $this->getDebug_DebugHandlersListenerService()) && false ?: '_'};
        }, 1 => 'configure'], 2048);
        $instance->addListener('kernel.request', [0 => function () {
            return ${($_ = isset($this->services['router_listener']) ? $this->services['router_listener'] : $this->getRouterListenerService()) && false ?: '_'};
        }, 1 => 'onKernelRequest'], 32);
        $instance->addListener('kernel.finish_request', [0 => function () {
            return ${($_ = isset($this->services['router_listener']) ? $this->services['router_listener'] : $this->getRouterListenerService()) && false ?: '_'};
        }, 1 => 'onKernelFinishRequest'], 0);
        $instance->addListener('kernel.exception', [0 => function () {
            return ${($_ = isset($this->services['router_listener']) ? $this->services['router_listener'] : $this->getRouterListenerService()) && false ?: '_'};
        }, 1 => 'onKernelException'], -64);
        $instance->addListener('kernel.response', [0 => function () {
            return ${($_ = isset($this->services['security.rememberme.response_listener']) ? $this->services['security.rememberme.response_listener'] : ($this->services['security.rememberme.response_listener'] = new \Symfony\Component\Security\Http\RememberMe\ResponseListener())) && false ?: '_'};
        }, 1 => 'onKernelResponse'], 0);
        $instance->addListener('kernel.request', [0 => function () {
            return ${($_ = isset($this->services['security.firewall']) ? $this->services['security.firewall'] : $this->getSecurity_FirewallService()) && false ?: '_'};
        }, 1 => 'onKernelRequest'], 8);
        $instance->addListener('kernel.finish_request', [0 => function () {
            return ${($_ = isset($this->services['security.firewall']) ? $this->services['security.firewall'] : $this->getSecurity_FirewallService()) && false ?: '_'};
        }, 1 => 'onKernelFinishRequest'], 0);
        $instance->addListener('kernel.exception', [0 => function () {
            return ${($_ = isset($this->services['twig.exception_listener']) ? $this->services['twig.exception_listener'] : $this->load('getTwig_ExceptionListenerService.php')) && false ?: '_'};
        }, 1 => 'onKernelException'], -128);
        $instance->addListener('console.command', [0 => function () {
            return ${($_ = isset($this->services['monolog.handler.console']) ? $this->services['monolog.handler.console'] : $this->getMonolog_Handler_ConsoleService()) && false ?: '_'};
        }, 1 => 'onCommand'], 255);
        $instance->addListener('console.terminate', [0 => function () {
            return ${($_ = isset($this->services['monolog.handler.console']) ? $this->services['monolog.handler.console'] : $this->getMonolog_Handler_ConsoleService()) && false ?: '_'};
        }, 1 => 'onTerminate'], -255);
        $instance->addListener('kernel.exception', [0 => function () {
            return ${($_ = isset($this->services['swiftmailer.email_sender.listener']) ? $this->services['swiftmailer.email_sender.listener'] : $this->load('getSwiftmailer_EmailSender_ListenerService.php')) && false ?: '_'};
        }, 1 => 'onException'], 0);
        $instance->addListener('kernel.terminate', [0 => function () {
            return ${($_ = isset($this->services['swiftmailer.email_sender.listener']) ? $this->services['swiftmailer.email_sender.listener'] : $this->load('getSwiftmailer_EmailSender_ListenerService.php')) && false ?: '_'};
        }, 1 => 'onTerminate'], 0);
        $instance->addListener('console.error', [0 => function () {
            return ${($_ = isset($this->services['swiftmailer.email_sender.listener']) ? $this->services['swiftmailer.email_sender.listener'] : $this->load('getSwiftmailer_EmailSender_ListenerService.php')) && false ?: '_'};
        }, 1 => 'onException'], 0);
        $instance->addListener('console.terminate', [0 => function () {
            return ${($_ = isset($this->services['swiftmailer.email_sender.listener']) ? $this->services['swiftmailer.email_sender.listener'] : $this->load('getSwiftmailer_EmailSender_ListenerService.php')) && false ?: '_'};
        }, 1 => 'onTerminate'], 0);
        $instance->addListener('kernel.controller', [0 => function () {
            return ${($_ = isset($this->services['sensio_framework_extra.controller.listener']) ? $this->services['sensio_framework_extra.controller.listener'] : $this->getSensioFrameworkExtra_Controller_ListenerService()) && false ?: '_'};
        }, 1 => 'onKernelController'], 0);
        $instance->addListener('kernel.controller', [0 => function () {
            return ${($_ = isset($this->services['sensio_framework_extra.converter.listener']) ? $this->services['sensio_framework_extra.converter.listener'] : $this->getSensioFrameworkExtra_Converter_ListenerService()) && false ?: '_'};
        }, 1 => 'onKernelController'], 0);
        $instance->addListener('kernel.controller', [0 => function () {
            return ${($_ = isset($this->services['sensio_framework_extra.view.listener']) ? $this->services['sensio_framework_extra.view.listener'] : ($this->services['sensio_framework_extra.view.listener'] = new \Sensio\Bundle\FrameworkExtraBundle\EventListener\TemplateListener($this))) && false ?: '_'};
        }, 1 => 'onKernelController'], -128);
        $instance->addListener('kernel.view', [0 => function () {
            return ${($_ = isset($this->services['sensio_framework_extra.view.listener']) ? $this->services['sensio_framework_extra.view.listener'] : ($this->services['sensio_framework_extra.view.listener'] = new \Sensio\Bundle\FrameworkExtraBundle\EventListener\TemplateListener($this))) && false ?: '_'};
        }, 1 => 'onKernelView'], 0);
        $instance->addListener('kernel.exception', [0 => function () {
            return ${($_ = isset($this->services['fos_rest.exception_listener']) ? $this->services['fos_rest.exception_listener'] : $this->load('getFosRest_ExceptionListenerService.php')) && false ?: '_'};
        }, 1 => 'onKernelException'], -100);
        $instance->addListener('kernel.view', [0 => function () {
            return ${($_ = isset($this->services['fos_rest.view_response_listener']) ? $this->services['fos_rest.view_response_listener'] : $this->load('getFosRest_ViewResponseListenerService.php')) && false ?: '_'};
        }, 1 => 'onKernelView'], 30);
        $instance->addListener('kernel.response', [0 => function () {
            return ${($_ = isset($this->services['nelmio_security.clickjacking_listener']) ? $this->services['nelmio_security.clickjacking_listener'] : $this->getNelmioSecurity_ClickjackingListenerService()) && false ?: '_'};
        }, 1 => 'onKernelResponse'], 0);
        $instance->addListener('kernel.response', [0 => function () {
            return ${($_ = isset($this->services['web_profiler.debug_toolbar']) ? $this->services['web_profiler.debug_toolbar'] : $this->getWebProfiler_DebugToolbarService()) && false ?: '_'};
        }, 1 => 'onKernelResponse'], -128);

        return $instance;
    }

    /**
     * Gets the private 'debug.file_link_formatter' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Debug\FileLinkFormatter
     */
    protected function getDebug_FileLinkFormatterService()
    {
        return $this->services['debug.file_link_formatter'] = new \Symfony\Component\HttpKernel\Debug\FileLinkFormatter(NULL, ${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : ($this->services['request_stack'] = new \Symfony\Component\HttpFoundation\RequestStack())) && false ?: '_'}, $this->targetDirs[4], function () {
            return ${($_ = isset($this->services['debug.file_link_formatter.url_format']) ? $this->services['debug.file_link_formatter.url_format'] : $this->load('getDebug_FileLinkFormatter_UrlFormatService.php')) && false ?: '_'};
        });
    }

    /**
     * Gets the private 'debug.security.access.decision_manager' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authorization\TraceableAccessDecisionManager
     */
    protected function getDebug_Security_Access_DecisionManagerService()
    {
        return $this->services['debug.security.access.decision_manager'] = new \Symfony\Component\Security\Core\Authorization\TraceableAccessDecisionManager(new \Symfony\Component\Security\Core\Authorization\AccessDecisionManager(new RewindableGenerator(function () {
            yield 0 => ${($_ = isset($this->services['security.access.authenticated_voter']) ? $this->services['security.access.authenticated_voter'] : $this->load('getSecurity_Access_AuthenticatedVoterService.php')) && false ?: '_'};
            yield 1 => ${($_ = isset($this->services['security.access.role_hierarchy_voter']) ? $this->services['security.access.role_hierarchy_voter'] : $this->load('getSecurity_Access_RoleHierarchyVoterService.php')) && false ?: '_'};
            yield 2 => ${($_ = isset($this->services['security.access.expression_voter']) ? $this->services['security.access.expression_voter'] : $this->load('getSecurity_Access_ExpressionVoterService.php')) && false ?: '_'};
            yield 3 => ${($_ = isset($this->services['Rialto\\Manufacturing\\BuildFiles\\PcbBuildFileVoter']) ? $this->services['Rialto\\Manufacturing\\BuildFiles\\PcbBuildFileVoter'] : $this->load('getPcbBuildFileVoterService.php')) && false ?: '_'};
            yield 4 => ${($_ = isset($this->services['Rialto\\Purchasing\\Order\\PurchaseOrderVoter']) ? $this->services['Rialto\\Purchasing\\Order\\PurchaseOrderVoter'] : $this->load('getPurchaseOrderVoterService.php')) && false ?: '_'};
            yield 5 => ${($_ = isset($this->services['Rialto\\Purchasing\\Order\\StockItemVoter']) ? $this->services['Rialto\\Purchasing\\Order\\StockItemVoter'] : $this->load('getStockItemVoterService.php')) && false ?: '_'};
            yield 6 => ${($_ = isset($this->services['Rialto\\Purchasing\\Producer\\StockProducerVoter']) ? $this->services['Rialto\\Purchasing\\Producer\\StockProducerVoter'] : $this->load('getStockProducerVoterService.php')) && false ?: '_'};
            yield 7 => ${($_ = isset($this->services['Rialto\\Purchasing\\Receiving\\Auth\\ReceiveIntoVoter']) ? $this->services['Rialto\\Purchasing\\Receiving\\Auth\\ReceiveIntoVoter'] : $this->load('getReceiveIntoVoterService.php')) && false ?: '_'};
            yield 8 => ${($_ = isset($this->services['Rialto\\Security\\User\\UserVoter']) ? $this->services['Rialto\\Security\\User\\UserVoter'] : $this->load('getUserVoterService.php')) && false ?: '_'};
            yield 9 => ${($_ = isset($this->services['Rialto\\Task\\TaskVoter']) ? $this->services['Rialto\\Task\\TaskVoter'] : $this->load('getTaskVoterService.php')) && false ?: '_'};
            yield 10 => ${($_ = isset($this->services['Rialto\\Stock\\Bin\\StockBinVoter']) ? $this->services['Rialto\\Stock\\Bin\\StockBinVoter'] : $this->load('getStockBinVoterService.php')) && false ?: '_'};
            yield 11 => ${($_ = isset($this->services['Rialto\\Stock\\Count\\StockCountVoter']) ? $this->services['Rialto\\Stock\\Count\\StockCountVoter'] : $this->load('getStockCountVoterService.php')) && false ?: '_'};
            yield 12 => ${($_ = isset($this->services['Rialto\\Summary\\Menu\\SummaryVoter']) ? $this->services['Rialto\\Summary\\Menu\\SummaryVoter'] : $this->load('getSummaryVoterService.php')) && false ?: '_'};
            yield 13 => ${($_ = isset($this->services['Rialto\\Supplier\\SupplierVoter']) ? $this->services['Rialto\\Supplier\\SupplierVoter'] : $this->load('getSupplierVoterService.php')) && false ?: '_'};
        }, 14), 'affirmative', false, true));
    }

    /**
     * Gets the private 'debug.stopwatch' shared service.
     *
     * @return \Symfony\Component\Stopwatch\Stopwatch
     */
    protected function getDebug_StopwatchService()
    {
        return $this->services['debug.stopwatch'] = new \Symfony\Component\Stopwatch\Stopwatch(true);
    }

    /**
     * Gets the private 'doctrine.dbal.connection_factory' shared service.
     *
     * @return \Doctrine\Bundle\DoctrineBundle\ConnectionFactory
     */
    protected function getDoctrine_Dbal_ConnectionFactoryService()
    {
        return $this->services['doctrine.dbal.connection_factory'] = new \Doctrine\Bundle\DoctrineBundle\ConnectionFactory($this->parameters['doctrine.dbal.connection_factory.types']);
    }

    /**
     * Gets the private 'doctrine.orm.default_entity_listener_resolver' shared service.
     *
     * @return \Doctrine\Bundle\DoctrineBundle\Mapping\ContainerEntityListenerResolver
     */
    protected function getDoctrine_Orm_DefaultEntityListenerResolverService()
    {
        return $this->services['doctrine.orm.default_entity_listener_resolver'] = new \Doctrine\Bundle\DoctrineBundle\Mapping\ContainerEntityListenerResolver($this);
    }

    /**
     * Gets the private 'doctrine.orm.default_listeners.attach_entity_listeners' shared service.
     *
     * @return \Doctrine\ORM\Tools\AttachEntityListenersListener
     */
    protected function getDoctrine_Orm_DefaultListeners_AttachEntityListenersService()
    {
        return $this->services['doctrine.orm.default_listeners.attach_entity_listeners'] = new \Doctrine\ORM\Tools\AttachEntityListenersListener();
    }

    /**
     * Gets the private 'doctrine.orm.default_manager_configurator' shared service.
     *
     * @return \Doctrine\Bundle\DoctrineBundle\ManagerConfigurator
     */
    protected function getDoctrine_Orm_DefaultManagerConfiguratorService()
    {
        return $this->services['doctrine.orm.default_manager_configurator'] = new \Doctrine\Bundle\DoctrineBundle\ManagerConfigurator([], []);
    }

    /**
     * Gets the private 'doctrine.orm.validator_initializer' shared service.
     *
     * @return \Symfony\Bridge\Doctrine\Validator\DoctrineInitializer
     */
    protected function getDoctrine_Orm_ValidatorInitializerService()
    {
        return $this->services['doctrine.orm.validator_initializer'] = new \Symfony\Bridge\Doctrine\Validator\DoctrineInitializer(${($_ = isset($this->services['doctrine']) ? $this->services['doctrine'] : $this->getDoctrineService()) && false ?: '_'});
    }

    /**
     * Gets the private 'file_locator' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Config\FileLocator
     */
    protected function getFileLocatorService()
    {
        return $this->services['file_locator'] = new \Symfony\Component\HttpKernel\Config\FileLocator(${($_ = isset($this->services['kernel']) ? $this->services['kernel'] : $this->get('kernel', 1)) && false ?: '_'}, ($this->targetDirs[4].'/app/Resources'), [0 => ($this->targetDirs[4].'/app')]);
    }

    /**
     * Gets the private 'form.registry' shared service.
     *
     * @return \Symfony\Component\Form\FormRegistry
     */
    protected function getForm_RegistryService()
    {
        return $this->services['form.registry'] = new \Symfony\Component\Form\FormRegistry([0 => new \Symfony\Component\Form\Extension\DependencyInjection\DependencyInjectionExtension(new \Symfony\Component\DependencyInjection\ServiceLocator(['EasyCorp\\Bundle\\EasyAdminBundle\\Form\\Type\\EasyAdminAutocompleteType' => function () {
            return ${($_ = isset($this->services['easyadmin.form.type.autocomplete']) ? $this->services['easyadmin.form.type.autocomplete'] : $this->load('getEasyadmin_Form_Type_AutocompleteService.php')) && false ?: '_'};
        }, 'EasyCorp\\Bundle\\EasyAdminBundle\\Form\\Type\\EasyAdminDividerType' => function () {
            return ${($_ = isset($this->services['easyadmin.form.type.divider']) ? $this->services['easyadmin.form.type.divider'] : ($this->services['easyadmin.form.type.divider'] = new \EasyCorp\Bundle\EasyAdminBundle\Form\Type\EasyAdminDividerType())) && false ?: '_'};
        }, 'EasyCorp\\Bundle\\EasyAdminBundle\\Form\\Type\\EasyAdminFormType' => function () {
            return ${($_ = isset($this->services['easyadmin.form.type']) ? $this->services['easyadmin.form.type'] : $this->load('getEasyadmin_Form_TypeService.php')) && false ?: '_'};
        }, 'EasyCorp\\Bundle\\EasyAdminBundle\\Form\\Type\\EasyAdminGroupType' => function () {
            return ${($_ = isset($this->services['easyadmin.form.type.group']) ? $this->services['easyadmin.form.type.group'] : ($this->services['easyadmin.form.type.group'] = new \EasyCorp\Bundle\EasyAdminBundle\Form\Type\EasyAdminGroupType())) && false ?: '_'};
        }, 'EasyCorp\\Bundle\\EasyAdminBundle\\Form\\Type\\EasyAdminSectionType' => function () {
            return ${($_ = isset($this->services['easyadmin.form.type.section']) ? $this->services['easyadmin.form.type.section'] : ($this->services['easyadmin.form.type.section'] = new \EasyCorp\Bundle\EasyAdminBundle\Form\Type\EasyAdminSectionType())) && false ?: '_'};
        }, 'Rialto\\Cms\\Web\\CmsEntryType' => function () {
            return ${($_ = isset($this->services['Rialto\\Cms\\Web\\CmsEntryType']) ? $this->services['Rialto\\Cms\\Web\\CmsEntryType'] : $this->load('getCmsEntryTypeService.php')) && false ?: '_'};
        }, 'Rialto\\Geography\\Address\\Web\\AddressEntityType' => function () {
            return ${($_ = isset($this->services['Rialto\\Geography\\Address\\Web\\AddressEntityType']) ? $this->services['Rialto\\Geography\\Address\\Web\\AddressEntityType'] : $this->load('getAddressEntityTypeService.php')) && false ?: '_'};
        }, 'Rialto\\Madison\\Feature\\Web\\FeatureType' => function () {
            return ${($_ = isset($this->services['Rialto\\Madison\\Feature\\Web\\FeatureType']) ? $this->services['Rialto\\Madison\\Feature\\Web\\FeatureType'] : $this->load('getFeatureTypeService.php')) && false ?: '_'};
        }, 'Rialto\\Manufacturing\\Customization\\Web\\CustomizationStrategyType' => function () {
            return ${($_ = isset($this->services['Rialto\\Manufacturing\\Customization\\Web\\CustomizationStrategyType']) ? $this->services['Rialto\\Manufacturing\\Customization\\Web\\CustomizationStrategyType'] : $this->load('getCustomizationStrategyTypeService.php')) && false ?: '_'};
        }, 'Rialto\\Purchasing\\Invoice\\Web\\SupplierInvoiceItemApprovalType' => function () {
            return ${($_ = isset($this->services['Rialto\\Purchasing\\Invoice\\Web\\SupplierInvoiceItemApprovalType']) ? $this->services['Rialto\\Purchasing\\Invoice\\Web\\SupplierInvoiceItemApprovalType'] : $this->load('getSupplierInvoiceItemApprovalTypeService.php')) && false ?: '_'};
        }, 'Rialto\\Purchasing\\Order\\Web\\CreatePurchaseOrderType' => function () {
            return ${($_ = isset($this->services['Rialto\\Purchasing\\Order\\Web\\CreatePurchaseOrderType']) ? $this->services['Rialto\\Purchasing\\Order\\Web\\CreatePurchaseOrderType'] : $this->load('getCreatePurchaseOrderTypeService.php')) && false ?: '_'};
        }, 'Rialto\\Purchasing\\Order\\Web\\EditPurchaseOrderType' => function () {
            return ${($_ = isset($this->services['Rialto\\Purchasing\\Order\\Web\\EditPurchaseOrderType']) ? $this->services['Rialto\\Purchasing\\Order\\Web\\EditPurchaseOrderType'] : $this->load('getEditPurchaseOrderTypeService.php')) && false ?: '_'};
        }, 'Rialto\\Purchasing\\Producer\\Web\\StockProducerType' => function () {
            return ${($_ = isset($this->services['Rialto\\Purchasing\\Producer\\Web\\StockProducerType']) ? $this->services['Rialto\\Purchasing\\Producer\\Web\\StockProducerType'] : $this->load('getStockProducerTypeService.php')) && false ?: '_'};
        }, 'Rialto\\Purchasing\\Receiving\\Web\\GoodsReceivedType' => function () {
            return ${($_ = isset($this->services['Rialto\\Purchasing\\Receiving\\Web\\GoodsReceivedType']) ? $this->services['Rialto\\Purchasing\\Receiving\\Web\\GoodsReceivedType'] : $this->load('getGoodsReceivedTypeService.php')) && false ?: '_'};
        }, 'Rialto\\Purchasing\\Supplier\\Attribute\\Web\\SupplierAttributeType' => function () {
            return ${($_ = isset($this->services['Rialto\\Purchasing\\Supplier\\Attribute\\Web\\SupplierAttributeType']) ? $this->services['Rialto\\Purchasing\\Supplier\\Attribute\\Web\\SupplierAttributeType'] : ($this->services['Rialto\\Purchasing\\Supplier\\Attribute\\Web\\SupplierAttributeType'] = new \Rialto\Purchasing\Supplier\Attribute\Web\SupplierAttributeType())) && false ?: '_'};
        }, 'Rialto\\Security\\User\\Web\\UserType' => function () {
            return ${($_ = isset($this->services['Rialto\\Security\\User\\Web\\UserType']) ? $this->services['Rialto\\Security\\User\\Web\\UserType'] : $this->load('getUserTypeService.php')) && false ?: '_'};
        }, 'Rialto\\Shipping\\Shipment\\Web\\ShipmentOptionsType' => function () {
            return ${($_ = isset($this->services['Rialto\\Shipping\\Shipment\\Web\\ShipmentOptionsType']) ? $this->services['Rialto\\Shipping\\Shipment\\Web\\ShipmentOptionsType'] : $this->load('getShipmentOptionsTypeService.php')) && false ?: '_'};
        }, 'Rialto\\Stock\\Bin\\Web\\BinUpdateAllocType' => function () {
            return ${($_ = isset($this->services['Rialto\\Stock\\Bin\\Web\\BinUpdateAllocType']) ? $this->services['Rialto\\Stock\\Bin\\Web\\BinUpdateAllocType'] : $this->load('getBinUpdateAllocTypeService.php')) && false ?: '_'};
        }, 'Rialto\\Stock\\Bin\\Web\\StockAdjustmentType' => function () {
            return ${($_ = isset($this->services['Rialto\\Stock\\Bin\\Web\\StockAdjustmentType']) ? $this->services['Rialto\\Stock\\Bin\\Web\\StockAdjustmentType'] : $this->load('getStockAdjustmentTypeService.php')) && false ?: '_'};
        }, 'Rialto\\Stock\\Item\\Version\\Web\\ItemVersionSelectorType' => function () {
            return ${($_ = isset($this->services['Rialto\\Stock\\Item\\Version\\Web\\ItemVersionSelectorType']) ? $this->services['Rialto\\Stock\\Item\\Version\\Web\\ItemVersionSelectorType'] : $this->load('getItemVersionSelectorTypeService.php')) && false ?: '_'};
        }, 'Rialto\\Stock\\Item\\Web\\EditType' => function () {
            return ${($_ = isset($this->services['Rialto\\Stock\\Item\\Web\\EditType']) ? $this->services['Rialto\\Stock\\Item\\Web\\EditType'] : $this->load('getEditTypeService.php')) && false ?: '_'};
        }, 'Rialto\\Stock\\Item\\Web\\StockItemAttributeType' => function () {
            return ${($_ = isset($this->services['Rialto\\Stock\\Item\\Web\\StockItemAttributeType']) ? $this->services['Rialto\\Stock\\Item\\Web\\StockItemAttributeType'] : ($this->services['Rialto\\Stock\\Item\\Web\\StockItemAttributeType'] = new \Rialto\Stock\Item\Web\StockItemAttributeType())) && false ?: '_'};
        }, 'Rialto\\Stock\\Item\\Web\\StockItemTemplateType' => function () {
            return ${($_ = isset($this->services['Rialto\\Stock\\Item\\Web\\StockItemTemplateType']) ? $this->services['Rialto\\Stock\\Item\\Web\\StockItemTemplateType'] : $this->load('getStockItemTemplateTypeService.php')) && false ?: '_'};
        }, 'Rialto\\Web\\Form\\JsEntityType' => function () {
            return ${($_ = isset($this->services['Rialto\\Web\\Form\\JsEntityType']) ? $this->services['Rialto\\Web\\Form\\JsEntityType'] : $this->load('getJsEntityTypeService.php')) && false ?: '_'};
        }, 'Rialto\\Web\\Form\\TextEntityType' => function () {
            return ${($_ = isset($this->services['Rialto\\Web\\Form\\TextEntityType']) ? $this->services['Rialto\\Web\\Form\\TextEntityType'] : $this->load('getTextEntityTypeService.php')) && false ?: '_'};
        }, 'Symfony\\Bridge\\Doctrine\\Form\\Type\\EntityType' => function () {
            return ${($_ = isset($this->services['form.type.entity']) ? $this->services['form.type.entity'] : $this->load('getForm_Type_EntityService.php')) && false ?: '_'};
        }, 'Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType' => function () {
            return ${($_ = isset($this->services['form.type.choice']) ? $this->services['form.type.choice'] : $this->load('getForm_Type_ChoiceService.php')) && false ?: '_'};
        }, 'Symfony\\Component\\Form\\Extension\\Core\\Type\\FileType' => function () {
            return ${($_ = isset($this->services['form.type.file']) ? $this->services['form.type.file'] : $this->load('getForm_Type_FileService.php')) && false ?: '_'};
        }, 'Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType' => function () {
            return ${($_ = isset($this->services['form.type.form']) ? $this->services['form.type.form'] : $this->load('getForm_Type_FormService.php')) && false ?: '_'};
        }]), ['Symfony\\Component\\Form\\Extension\\Core\\Type\\NumberType' => new RewindableGenerator(function () {
            yield 0 => ${($_ = isset($this->services['Rialto\\Web\\Form\\NumberTypeExtension']) ? $this->services['Rialto\\Web\\Form\\NumberTypeExtension'] : ($this->services['Rialto\\Web\\Form\\NumberTypeExtension'] = new \Rialto\Web\Form\NumberTypeExtension())) && false ?: '_'};
        }, 1), 'Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType' => new RewindableGenerator(function () {
            yield 0 => ${($_ = isset($this->services['form.type_extension.form.transformation_failure_handling']) ? $this->services['form.type_extension.form.transformation_failure_handling'] : $this->load('getForm_TypeExtension_Form_TransformationFailureHandlingService.php')) && false ?: '_'};
            yield 1 => ${($_ = isset($this->services['form.type_extension.form.http_foundation']) ? $this->services['form.type_extension.form.http_foundation'] : $this->load('getForm_TypeExtension_Form_HttpFoundationService.php')) && false ?: '_'};
            yield 2 => ${($_ = isset($this->services['form.type_extension.form.validator']) ? $this->services['form.type_extension.form.validator'] : $this->load('getForm_TypeExtension_Form_ValidatorService.php')) && false ?: '_'};
            yield 3 => ${($_ = isset($this->services['form.type_extension.upload.validator']) ? $this->services['form.type_extension.upload.validator'] : $this->load('getForm_TypeExtension_Upload_ValidatorService.php')) && false ?: '_'};
            yield 4 => ${($_ = isset($this->services['form.type_extension.csrf']) ? $this->services['form.type_extension.csrf'] : $this->load('getForm_TypeExtension_CsrfService.php')) && false ?: '_'};
            yield 5 => ${($_ = isset($this->services['craue.form.flow.form_extension']) ? $this->services['craue.form.flow.form_extension'] : ($this->services['craue.form.flow.form_extension'] = new \Craue\FormFlowBundle\Form\Extension\FormFlowFormExtension())) && false ?: '_'};
            yield 6 => ${($_ = isset($this->services['easyadmin.form.type.extension']) ? $this->services['easyadmin.form.type.extension'] : $this->load('getEasyadmin_Form_Type_ExtensionService.php')) && false ?: '_'};
        }, 7), 'Symfony\\Component\\Form\\Extension\\Core\\Type\\RepeatedType' => new RewindableGenerator(function () {
            yield 0 => ${($_ = isset($this->services['form.type_extension.repeated.validator']) ? $this->services['form.type_extension.repeated.validator'] : ($this->services['form.type_extension.repeated.validator'] = new \Symfony\Component\Form\Extension\Validator\Type\RepeatedTypeValidatorExtension())) && false ?: '_'};
        }, 1), 'Symfony\\Component\\Form\\Extension\\Core\\Type\\SubmitType' => new RewindableGenerator(function () {
            yield 0 => ${($_ = isset($this->services['form.type_extension.submit.validator']) ? $this->services['form.type_extension.submit.validator'] : ($this->services['form.type_extension.submit.validator'] = new \Symfony\Component\Form\Extension\Validator\Type\SubmitTypeValidatorExtension())) && false ?: '_'};
        }, 1), 'Symfony\\Component\\Form\\Extension\\Core\\Type\\HiddenType' => new RewindableGenerator(function () {
            yield 0 => ${($_ = isset($this->services['craue.form.flow.hidden_field_extension']) ? $this->services['craue.form.flow.hidden_field_extension'] : ($this->services['craue.form.flow.hidden_field_extension'] = new \Craue\FormFlowBundle\Form\Extension\FormFlowHiddenFieldExtension())) && false ?: '_'};
        }, 1)], new RewindableGenerator(function () {
            yield 0 => ${($_ = isset($this->services['form.type_guesser.validator']) ? $this->services['form.type_guesser.validator'] : $this->load('getForm_TypeGuesser_ValidatorService.php')) && false ?: '_'};
            yield 1 => ${($_ = isset($this->services['form.type_guesser.doctrine']) ? $this->services['form.type_guesser.doctrine'] : $this->load('getForm_TypeGuesser_DoctrineService.php')) && false ?: '_'};
            yield 2 => ${($_ = isset($this->services['easyadmin.form.guesser.missing_doctrine_orm_type_guesser']) ? $this->services['easyadmin.form.guesser.missing_doctrine_orm_type_guesser'] : $this->load('getEasyadmin_Form_Guesser_MissingDoctrineOrmTypeGuesserService.php')) && false ?: '_'};
        }, 3), NULL)], ${($_ = isset($this->services['form.resolved_type_factory']) ? $this->services['form.resolved_type_factory'] : ($this->services['form.resolved_type_factory'] = new \Symfony\Component\Form\ResolvedFormTypeFactory())) && false ?: '_'});
    }

    /**
     * Gets the private 'form.resolved_type_factory' shared service.
     *
     * @return \Symfony\Component\Form\ResolvedFormTypeFactory
     */
    protected function getForm_ResolvedTypeFactoryService()
    {
        return $this->services['form.resolved_type_factory'] = new \Symfony\Component\Form\ResolvedFormTypeFactory();
    }

    /**
     * Gets the private 'fos_rest.body_listener' shared service.
     *
     * @return \FOS\RestBundle\EventListener\BodyListener
     */
    protected function getFosRest_BodyListenerService()
    {
        $this->services['fos_rest.body_listener'] = $instance = new \FOS\RestBundle\EventListener\BodyListener(${($_ = isset($this->services['fos_rest.decoder_provider']) ? $this->services['fos_rest.decoder_provider'] : $this->getFosRest_DecoderProviderService()) && false ?: '_'}, false);

        $instance->setDefaultFormat(NULL);

        return $instance;
    }

    /**
     * Gets the private 'fos_rest.decoder_provider' shared service.
     *
     * @return \FOS\RestBundle\Decoder\ContainerDecoderProvider
     */
    protected function getFosRest_DecoderProviderService()
    {
        return $this->services['fos_rest.decoder_provider'] = new \FOS\RestBundle\Decoder\ContainerDecoderProvider(new \Symfony\Component\DependencyInjection\ServiceLocator(['fos_rest.decoder.json' => function () {
            return ${($_ = isset($this->services['fos_rest.decoder.json']) ? $this->services['fos_rest.decoder.json'] : ($this->services['fos_rest.decoder.json'] = new \FOS\RestBundle\Decoder\JsonDecoder())) && false ?: '_'};
        }]), ['json' => 'fos_rest.decoder.json']);
    }

    /**
     * Gets the private 'fos_rest.format_listener' shared service.
     *
     * @return \FOS\RestBundle\EventListener\FormatListener
     */
    protected function getFosRest_FormatListenerService()
    {
        return $this->services['fos_rest.format_listener'] = new \FOS\RestBundle\EventListener\FormatListener(${($_ = isset($this->services['fos_rest.format_negotiator']) ? $this->services['fos_rest.format_negotiator'] : $this->getFosRest_FormatNegotiatorService()) && false ?: '_'});
    }

    /**
     * Gets the private 'fos_rest.format_negotiator' shared service.
     *
     * @return \FOS\RestBundle\Negotiation\FormatNegotiator
     */
    protected function getFosRest_FormatNegotiatorService()
    {
        $this->services['fos_rest.format_negotiator'] = $instance = new \FOS\RestBundle\Negotiation\FormatNegotiator(${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : ($this->services['request_stack'] = new \Symfony\Component\HttpFoundation\RequestStack())) && false ?: '_'});

        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/_profiler|_wdt/', NULL, NULL, NULL, []), ['methods' => NULL, 'priorities' => [0 => 'html', 1 => 'json'], 'fallback_format' => 'html', 'attributes' => [], 'prefer_extension' => '2.0']);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/api', NULL, NULL, NULL, []), ['priorities' => [0 => 'json', 1 => 'html'], 'fallback_format' => 'json', 'prefer_extension' => '2.0', 'methods' => NULL, 'attributes' => [], 'stop' => false]);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/css', NULL, NULL, NULL, []), ['priorities' => [0 => 'css'], 'methods' => NULL, 'attributes' => [], 'stop' => false, 'prefer_extension' => '2.0', 'fallback_format' => 'html']);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/js', NULL, NULL, NULL, []), ['priorities' => [0 => 'js'], 'methods' => NULL, 'attributes' => [], 'stop' => false, 'prefer_extension' => '2.0', 'fallback_format' => 'html']);
        $instance->add(new \Symfony\Component\HttpFoundation\RequestMatcher('^/', NULL, NULL, NULL, []), ['priorities' => [0 => 'html', 1 => 'json'], 'fallback_format' => 'html', 'prefer_extension' => '2.0', 'methods' => NULL, 'attributes' => [], 'stop' => false]);

        return $instance;
    }

    /**
     * Gets the private 'gumstix_form.twig_extension' shared service.
     *
     * @return \Gumstix\FormBundle\Twig\FormExtension
     */
    protected function getGumstixForm_TwigExtensionService()
    {
        return $this->services['gumstix_form.twig_extension'] = new \Gumstix\FormBundle\Twig\FormExtension();
    }

    /**
     * Gets the private 'gumstix_sso.router' shared service.
     *
     * @return \Gumstix\SSOBundle\Service\SSORouter
     */
    protected function getGumstixSso_RouterService()
    {
        return $this->services['gumstix_sso.router'] = new \Gumstix\SSOBundle\Service\SSORouter('http://accounts.mystix.com/');
    }

    /**
     * Gets the private 'jms_job_queue.twig.extension' shared service.
     *
     * @return \JMS\JobQueueBundle\Twig\JobQueueExtension
     */
    protected function getJmsJobQueue_Twig_ExtensionService()
    {
        return $this->services['jms_job_queue.twig.extension'] = new \JMS\JobQueueBundle\Twig\JobQueueExtension([]);
    }

    /**
     * Gets the private 'locale_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\LocaleListener
     */
    protected function getLocaleListenerService()
    {
        return $this->services['locale_listener'] = new \Symfony\Component\HttpKernel\EventListener\LocaleListener(${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : ($this->services['request_stack'] = new \Symfony\Component\HttpFoundation\RequestStack())) && false ?: '_'}, 'en', ${($_ = isset($this->services['Symfony\\Component\\Routing\\RouterInterface']) ? $this->services['Symfony\\Component\\Routing\\RouterInterface'] : $this->getRouterInterfaceService()) && false ?: '_'});
    }

    /**
     * Gets the private 'monolog.handler.console' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Handler\ConsoleHandler
     */
    protected function getMonolog_Handler_ConsoleService()
    {
        $this->services['monolog.handler.console'] = $instance = new \Symfony\Bridge\Monolog\Handler\ConsoleHandler(NULL, true, [], []);

        $instance->pushProcessor(${($_ = isset($this->services['monolog.processor.psr_log_message']) ? $this->services['monolog.processor.psr_log_message'] : ($this->services['monolog.processor.psr_log_message'] = new \Monolog\Processor\PsrLogMessageProcessor())) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the private 'monolog.handler.doctrine' shared service.
     *
     * @return \Monolog\Handler\StreamHandler
     */
    protected function getMonolog_Handler_DoctrineService()
    {
        $this->services['monolog.handler.doctrine'] = $instance = new \Monolog\Handler\StreamHandler(($this->targetDirs[3].'/logs/www-data/doctrine.log'), 100, true, NULL, false);

        $instance->pushProcessor(${($_ = isset($this->services['monolog.processor.psr_log_message']) ? $this->services['monolog.processor.psr_log_message'] : ($this->services['monolog.processor.psr_log_message'] = new \Monolog\Processor\PsrLogMessageProcessor())) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the private 'monolog.handler.php' shared service.
     *
     * @return \Monolog\Handler\ErrorLogHandler
     */
    protected function getMonolog_Handler_PhpService()
    {
        $this->services['monolog.handler.php'] = $instance = new \Monolog\Handler\ErrorLogHandler(0, 250, true);

        $instance->pushProcessor(${($_ = isset($this->services['monolog.processor.psr_log_message']) ? $this->services['monolog.processor.psr_log_message'] : ($this->services['monolog.processor.psr_log_message'] = new \Monolog\Processor\PsrLogMessageProcessor())) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the private 'monolog.handler.sentry' shared service.
     *
     * @return \Monolog\Handler\ErrorLogHandler
     */
    protected function getMonolog_Handler_SentryService()
    {
        $this->services['monolog.handler.sentry'] = $instance = new \Monolog\Handler\ErrorLogHandler(0, 400, true);

        $instance->pushProcessor(${($_ = isset($this->services['monolog.processor.psr_log_message']) ? $this->services['monolog.processor.psr_log_message'] : ($this->services['monolog.processor.psr_log_message'] = new \Monolog\Processor\PsrLogMessageProcessor())) && false ?: '_'});
        $instance->pushProcessor([0 => new \Rialto\Security\Logging\SentryContextProcessor(${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : ($this->services['security.token_storage'] = new \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage())) && false ?: '_'}), 1 => 'processRecord']);

        return $instance;
    }

    /**
     * Gets the private 'monolog.logger.doctrine' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonolog_Logger_DoctrineService()
    {
        $this->services['monolog.logger.doctrine'] = $instance = new \Symfony\Bridge\Monolog\Logger('doctrine');

        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.doctrine']) ? $this->services['monolog.handler.doctrine'] : $this->getMonolog_Handler_DoctrineService()) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.sentry']) ? $this->services['monolog.handler.sentry'] : $this->getMonolog_Handler_SentryService()) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the private 'monolog.logger.event' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonolog_Logger_EventService()
    {
        $this->services['monolog.logger.event'] = $instance = new \Symfony\Bridge\Monolog\Logger('event');

        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.console']) ? $this->services['monolog.handler.console'] : $this->getMonolog_Handler_ConsoleService()) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.sentry']) ? $this->services['monolog.handler.sentry'] : $this->getMonolog_Handler_SentryService()) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the private 'monolog.logger.php' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonolog_Logger_PhpService()
    {
        $this->services['monolog.logger.php'] = $instance = new \Symfony\Bridge\Monolog\Logger('php');

        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.console']) ? $this->services['monolog.handler.console'] : $this->getMonolog_Handler_ConsoleService()) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.php']) ? $this->services['monolog.handler.php'] : $this->getMonolog_Handler_PhpService()) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.sentry']) ? $this->services['monolog.handler.sentry'] : $this->getMonolog_Handler_SentryService()) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the private 'monolog.logger.request' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonolog_Logger_RequestService()
    {
        $this->services['monolog.logger.request'] = $instance = new \Symfony\Bridge\Monolog\Logger('request');

        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.console']) ? $this->services['monolog.handler.console'] : $this->getMonolog_Handler_ConsoleService()) && false ?: '_'});
        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.sentry']) ? $this->services['monolog.handler.sentry'] : $this->getMonolog_Handler_SentryService()) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the private 'monolog.logger.translation' shared service.
     *
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getMonolog_Logger_TranslationService()
    {
        $this->services['monolog.logger.translation'] = $instance = new \Symfony\Bridge\Monolog\Logger('translation');

        $instance->pushHandler(${($_ = isset($this->services['monolog.handler.sentry']) ? $this->services['monolog.handler.sentry'] : $this->getMonolog_Handler_SentryService()) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the private 'monolog.processor.psr_log_message' shared service.
     *
     * @return \Monolog\Processor\PsrLogMessageProcessor
     */
    protected function getMonolog_Processor_PsrLogMessageService()
    {
        return $this->services['monolog.processor.psr_log_message'] = new \Monolog\Processor\PsrLogMessageProcessor();
    }

    /**
     * Gets the private 'nelmio_cors.cors_listener' shared service.
     *
     * @return \Nelmio\CorsBundle\EventListener\CorsListener
     */
    protected function getNelmioCors_CorsListenerService()
    {
        return $this->services['nelmio_cors.cors_listener'] = new \Nelmio\CorsBundle\EventListener\CorsListener(${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->getDebug_EventDispatcherService()) && false ?: '_'}, new \Nelmio\CorsBundle\Options\Resolver([0 => ${($_ = isset($this->services['nelmio_cors.options_provider.config']) ? $this->services['nelmio_cors.options_provider.config'] : $this->getNelmioCors_OptionsProvider_ConfigService()) && false ?: '_'}]));
    }

    /**
     * Gets the private 'nelmio_cors.options_provider.config' shared service.
     *
     * @return \Nelmio\CorsBundle\Options\ConfigProvider
     */
    protected function getNelmioCors_OptionsProvider_ConfigService()
    {
        return $this->services['nelmio_cors.options_provider.config'] = new \Nelmio\CorsBundle\Options\ConfigProvider($this->parameters['nelmio_cors.map'], $this->parameters['nelmio_cors.defaults']);
    }

    /**
     * Gets the private 'nelmio_security.clickjacking_listener' shared service.
     *
     * @return \Nelmio\SecurityBundle\EventListener\ClickjackingListener
     */
    protected function getNelmioSecurity_ClickjackingListenerService()
    {
        return $this->services['nelmio_security.clickjacking_listener'] = new \Nelmio\SecurityBundle\EventListener\ClickjackingListener($this->parameters['nelmio_security.clickjacking.paths'], []);
    }

    /**
     * Gets the private 'nelmio_security.external_redirect.target_validator' shared service.
     *
     * @return \Nelmio\SecurityBundle\ExternalRedirect\WhitelistBasedTargetValidator
     */
    protected function getNelmioSecurity_ExternalRedirect_TargetValidatorService()
    {
        return $this->services['nelmio_security.external_redirect.target_validator'] = new \Nelmio\SecurityBundle\ExternalRedirect\WhitelistBasedTargetValidator('(?:.*\\.mystix\\.com|.*\\.dev\\-storefront\\.pcbng\\.com|mystix\\.com|dev\\-storefront\\.pcbng\\.com)');
    }

    /**
     * Gets the private 'nelmio_security.external_redirect_listener' shared service.
     *
     * @return \Nelmio\SecurityBundle\EventListener\ExternalRedirectListener
     */
    protected function getNelmioSecurity_ExternalRedirectListenerService()
    {
        return $this->services['nelmio_security.external_redirect_listener'] = new \Nelmio\SecurityBundle\EventListener\ExternalRedirectListener(true, NULL, NULL, ${($_ = isset($this->services['nelmio_security.external_redirect.target_validator']) ? $this->services['nelmio_security.external_redirect.target_validator'] : ($this->services['nelmio_security.external_redirect.target_validator'] = new \Nelmio\SecurityBundle\ExternalRedirect\WhitelistBasedTargetValidator('(?:.*\\.mystix\\.com|.*\\.dev\\-storefront\\.pcbng\\.com|mystix\\.com|dev\\-storefront\\.pcbng\\.com)'))) && false ?: '_'}, NULL, ${($_ = isset($this->services['Symfony\\Component\\Routing\\RouterInterface']) ? $this->services['Symfony\\Component\\Routing\\RouterInterface'] : $this->getRouterInterfaceService()) && false ?: '_'});
    }

    /**
     * Gets the private 'property_accessor' shared service.
     *
     * @return \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    protected function getPropertyAccessorService()
    {
        return $this->services['property_accessor'] = new \Symfony\Component\PropertyAccess\PropertyAccessor(false, false, new \Symfony\Component\Cache\Adapter\ArrayAdapter(0, false));
    }

    /**
     * Gets the private 'resolve_controller_name_subscriber' shared service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\EventListener\ResolveControllerNameSubscriber
     */
    protected function getResolveControllerNameSubscriberService()
    {
        return $this->services['resolve_controller_name_subscriber'] = new \Symfony\Bundle\FrameworkBundle\EventListener\ResolveControllerNameSubscriber(${($_ = isset($this->services['controller_name_converter']) ? $this->services['controller_name_converter'] : ($this->services['controller_name_converter'] = new \Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser(${($_ = isset($this->services['kernel']) ? $this->services['kernel'] : $this->get('kernel', 1)) && false ?: '_'}))) && false ?: '_'});
    }

    /**
     * Gets the private 'response_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\ResponseListener
     */
    protected function getResponseListenerService()
    {
        return $this->services['response_listener'] = new \Symfony\Component\HttpKernel\EventListener\ResponseListener('UTF-8');
    }

    /**
     * Gets the private 'router.request_context' shared service.
     *
     * @return \Symfony\Component\Routing\RequestContext
     */
    protected function getRouter_RequestContextService()
    {
        return $this->services['router.request_context'] = new \Symfony\Component\Routing\RequestContext('/index.php', 'GET', 'rialto.mystix.com', 'http', 80, 443);
    }

    /**
     * Gets the private 'router_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\RouterListener
     */
    protected function getRouterListenerService()
    {
        return $this->services['router_listener'] = new \Symfony\Component\HttpKernel\EventListener\RouterListener(${($_ = isset($this->services['Symfony\\Component\\Routing\\RouterInterface']) ? $this->services['Symfony\\Component\\Routing\\RouterInterface'] : $this->getRouterInterfaceService()) && false ?: '_'}, ${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : ($this->services['request_stack'] = new \Symfony\Component\HttpFoundation\RequestStack())) && false ?: '_'}, ${($_ = isset($this->services['router.request_context']) ? $this->services['router.request_context'] : $this->getRouter_RequestContextService()) && false ?: '_'}, ${($_ = isset($this->services['monolog.logger.request']) ? $this->services['monolog.logger.request'] : $this->getMonolog_Logger_RequestService()) && false ?: '_'}, $this->targetDirs[4], true);
    }

    /**
     * Gets the private 'security.authentication.manager' shared service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager
     */
    protected function getSecurity_Authentication_ManagerService()
    {
        $this->services['security.authentication.manager'] = $instance = new \Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager(new RewindableGenerator(function () {
            yield 0 => ${($_ = isset($this->services['security.authentication.provider.simple_preauth.shopify_webhook']) ? $this->services['security.authentication.provider.simple_preauth.shopify_webhook'] : $this->load('getSecurity_Authentication_Provider_SimplePreauth_ShopifyWebhookService.php')) && false ?: '_'};
            yield 1 => ${($_ = isset($this->services['security.authentication.provider.simple_preauth.magento2_oauth_callback']) ? $this->services['security.authentication.provider.simple_preauth.magento2_oauth_callback'] : $this->load('getSecurity_Authentication_Provider_SimplePreauth_Magento2OauthCallbackService.php')) && false ?: '_'};
            yield 2 => ${($_ = isset($this->services['security.authentication.provider.guard.api']) ? $this->services['security.authentication.provider.guard.api'] : $this->load('getSecurity_Authentication_Provider_Guard_ApiService.php')) && false ?: '_'};
            yield 3 => ${($_ = isset($this->services['security.authentication.provider.guard.main']) ? $this->services['security.authentication.provider.guard.main'] : $this->load('getSecurity_Authentication_Provider_Guard_MainService.php')) && false ?: '_'};
        }, 4), true);

        $instance->setEventDispatcher(${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->getDebug_EventDispatcherService()) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the private 'security.firewall' shared service.
     *
     * @return \Symfony\Bundle\SecurityBundle\Debug\TraceableFirewallListener
     */
    protected function getSecurity_FirewallService()
    {
        return $this->services['security.firewall'] = new \Symfony\Bundle\SecurityBundle\Debug\TraceableFirewallListener(new \Symfony\Bundle\SecurityBundle\Security\FirewallMap(new \Symfony\Component\DependencyInjection\ServiceLocator(['security.firewall.map.context.api' => function () {
            return ${($_ = isset($this->services['security.firewall.map.context.api']) ? $this->services['security.firewall.map.context.api'] : $this->load('getSecurity_Firewall_Map_Context_ApiService.php')) && false ?: '_'};
        }, 'security.firewall.map.context.magento2_oauth_callback' => function () {
            return ${($_ = isset($this->services['security.firewall.map.context.magento2_oauth_callback']) ? $this->services['security.firewall.map.context.magento2_oauth_callback'] : $this->load('getSecurity_Firewall_Map_Context_Magento2OauthCallbackService.php')) && false ?: '_'};
        }, 'security.firewall.map.context.main' => function () {
            return ${($_ = isset($this->services['security.firewall.map.context.main']) ? $this->services['security.firewall.map.context.main'] : $this->load('getSecurity_Firewall_Map_Context_MainService.php')) && false ?: '_'};
        }, 'security.firewall.map.context.shopify_webhook' => function () {
            return ${($_ = isset($this->services['security.firewall.map.context.shopify_webhook']) ? $this->services['security.firewall.map.context.shopify_webhook'] : $this->load('getSecurity_Firewall_Map_Context_ShopifyWebhookService.php')) && false ?: '_'};
        }]), new RewindableGenerator(function () {
            yield 'security.firewall.map.context.shopify_webhook' => ${($_ = isset($this->services['security.request_matcher.umgy0tl']) ? $this->services['security.request_matcher.umgy0tl'] : ($this->services['security.request_matcher.umgy0tl'] = new \Symfony\Component\HttpFoundation\RequestMatcher('^/api/shopify/webhook'))) && false ?: '_'};
            yield 'security.firewall.map.context.magento2_oauth_callback' => ${($_ = isset($this->services['security.request_matcher.kxgqwfa']) ? $this->services['security.request_matcher.kxgqwfa'] : ($this->services['security.request_matcher.kxgqwfa'] = new \Symfony\Component\HttpFoundation\RequestMatcher('^/magento2/oauth/callback'))) && false ?: '_'};
            yield 'security.firewall.map.context.api' => ${($_ = isset($this->services['security.request_matcher.x1icpav']) ? $this->services['security.request_matcher.x1icpav'] : ($this->services['security.request_matcher.x1icpav'] = new \Symfony\Component\HttpFoundation\RequestMatcher('^/api'))) && false ?: '_'};
            yield 'security.firewall.map.context.main' => ${($_ = isset($this->services['security.request_matcher.00qf1z7']) ? $this->services['security.request_matcher.00qf1z7'] : ($this->services['security.request_matcher.00qf1z7'] = new \Symfony\Component\HttpFoundation\RequestMatcher('^/'))) && false ?: '_'};
        }, 4)), ${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->getDebug_EventDispatcherService()) && false ?: '_'}, ${($_ = isset($this->services['security.logout_url_generator']) ? $this->services['security.logout_url_generator'] : $this->getSecurity_LogoutUrlGeneratorService()) && false ?: '_'});
    }

    /**
     * Gets the private 'security.logout_url_generator' shared service.
     *
     * @return \Symfony\Component\Security\Http\Logout\LogoutUrlGenerator
     */
    protected function getSecurity_LogoutUrlGeneratorService()
    {
        $this->services['security.logout_url_generator'] = $instance = new \Symfony\Component\Security\Http\Logout\LogoutUrlGenerator(${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : ($this->services['request_stack'] = new \Symfony\Component\HttpFoundation\RequestStack())) && false ?: '_'}, ${($_ = isset($this->services['Symfony\\Component\\Routing\\RouterInterface']) ? $this->services['Symfony\\Component\\Routing\\RouterInterface'] : $this->getRouterInterfaceService()) && false ?: '_'}, ${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : ($this->services['security.token_storage'] = new \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage())) && false ?: '_'});

        $instance->registerListener('main', '/logout', 'logout', '_csrf_token', NULL, NULL);

        return $instance;
    }

    /**
     * Gets the private 'security.rememberme.response_listener' shared service.
     *
     * @return \Symfony\Component\Security\Http\RememberMe\ResponseListener
     */
    protected function getSecurity_Rememberme_ResponseListenerService()
    {
        return $this->services['security.rememberme.response_listener'] = new \Symfony\Component\Security\Http\RememberMe\ResponseListener();
    }

    /**
     * Gets the private 'sensio_framework_extra.controller.listener' shared service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener
     */
    protected function getSensioFrameworkExtra_Controller_ListenerService()
    {
        return $this->services['sensio_framework_extra.controller.listener'] = new \Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener(${($_ = isset($this->services['annotation_reader']) ? $this->services['annotation_reader'] : $this->getAnnotationReaderService()) && false ?: '_'});
    }

    /**
     * Gets the private 'sensio_framework_extra.converter.datetime' shared service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DateTimeParamConverter
     */
    protected function getSensioFrameworkExtra_Converter_DatetimeService()
    {
        return $this->services['sensio_framework_extra.converter.datetime'] = new \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DateTimeParamConverter();
    }

    /**
     * Gets the private 'sensio_framework_extra.converter.doctrine.orm' shared service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter
     */
    protected function getSensioFrameworkExtra_Converter_Doctrine_OrmService()
    {
        return $this->services['sensio_framework_extra.converter.doctrine.orm'] = new \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter(${($_ = isset($this->services['doctrine']) ? $this->services['doctrine'] : $this->getDoctrineService()) && false ?: '_'});
    }

    /**
     * Gets the private 'sensio_framework_extra.converter.listener' shared service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener
     */
    protected function getSensioFrameworkExtra_Converter_ListenerService()
    {
        return $this->services['sensio_framework_extra.converter.listener'] = new \Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener(${($_ = isset($this->services['sensio_framework_extra.converter.manager']) ? $this->services['sensio_framework_extra.converter.manager'] : $this->getSensioFrameworkExtra_Converter_ManagerService()) && false ?: '_'}, true);
    }

    /**
     * Gets the private 'sensio_framework_extra.converter.manager' shared service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager
     */
    protected function getSensioFrameworkExtra_Converter_ManagerService()
    {
        $this->services['sensio_framework_extra.converter.manager'] = $instance = new \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager();

        $instance->add(new \Rialto\Web\LockingParamConverter(${($_ = isset($this->services['doctrine']) ? $this->services['doctrine'] : $this->getDoctrineService()) && false ?: '_'}), 10, NULL);
        $instance->add(${($_ = isset($this->services['sensio_framework_extra.converter.doctrine.orm']) ? $this->services['sensio_framework_extra.converter.doctrine.orm'] : $this->getSensioFrameworkExtra_Converter_Doctrine_OrmService()) && false ?: '_'}, 0, 'doctrine.orm');
        $instance->add(${($_ = isset($this->services['sensio_framework_extra.converter.datetime']) ? $this->services['sensio_framework_extra.converter.datetime'] : ($this->services['sensio_framework_extra.converter.datetime'] = new \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DateTimeParamConverter())) && false ?: '_'}, 0, 'datetime');

        return $instance;
    }

    /**
     * Gets the private 'sensio_framework_extra.view.listener' shared service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\EventListener\TemplateListener
     */
    protected function getSensioFrameworkExtra_View_ListenerService()
    {
        return $this->services['sensio_framework_extra.view.listener'] = new \Sensio\Bundle\FrameworkExtraBundle\EventListener\TemplateListener($this);
    }

    /**
     * Gets the private 'session.save_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\SaveSessionListener
     */
    protected function getSession_SaveListenerService()
    {
        return $this->services['session.save_listener'] = new \Symfony\Component\HttpKernel\EventListener\SaveSessionListener();
    }

    /**
     * Gets the private 'session_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\SessionListener
     */
    protected function getSessionListenerService()
    {
        return $this->services['session_listener'] = new \Symfony\Component\HttpKernel\EventListener\SessionListener(new \Symfony\Component\DependencyInjection\ServiceLocator(['session' => function () {
            return ${($_ = isset($this->services['session']) ? $this->services['session'] : $this->load('getSessionService.php')) && false ?: '_'};
        }]));
    }

    /**
     * Gets the private 'streamed_response_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\StreamedResponseListener
     */
    protected function getStreamedResponseListenerService()
    {
        return $this->services['streamed_response_listener'] = new \Symfony\Component\HttpKernel\EventListener\StreamedResponseListener();
    }

    /**
     * Gets the private 'templating.locator' shared service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator
     */
    protected function getTemplating_LocatorService()
    {
        return $this->services['templating.locator'] = new \Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator(${($_ = isset($this->services['file_locator']) ? $this->services['file_locator'] : ($this->services['file_locator'] = new \Symfony\Component\HttpKernel\Config\FileLocator(${($_ = isset($this->services['kernel']) ? $this->services['kernel'] : $this->get('kernel', 1)) && false ?: '_'}, ($this->targetDirs[4].'/app/Resources'), [0 => ($this->targetDirs[4].'/app')]))) && false ?: '_'}, $this->targetDirs[0]);
    }

    /**
     * Gets the private 'templating.name_parser' shared service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Templating\TemplateNameParser
     */
    protected function getTemplating_NameParserService()
    {
        return $this->services['templating.name_parser'] = new \Symfony\Bundle\FrameworkBundle\Templating\TemplateNameParser(${($_ = isset($this->services['kernel']) ? $this->services['kernel'] : $this->get('kernel', 1)) && false ?: '_'});
    }

    /**
     * Gets the private 'translator.default' shared service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    protected function getTranslator_DefaultService()
    {
        $this->services['translator.default'] = $instance = new \Symfony\Bundle\FrameworkBundle\Translation\Translator(new \Symfony\Component\DependencyInjection\ServiceLocator(['translation.loader.csv' => function () {
            return ${($_ = isset($this->services['translation.loader.csv']) ? $this->services['translation.loader.csv'] : ($this->services['translation.loader.csv'] = new \Symfony\Component\Translation\Loader\CsvFileLoader())) && false ?: '_'};
        }, 'translation.loader.dat' => function () {
            return ${($_ = isset($this->services['translation.loader.dat']) ? $this->services['translation.loader.dat'] : ($this->services['translation.loader.dat'] = new \Symfony\Component\Translation\Loader\IcuDatFileLoader())) && false ?: '_'};
        }, 'translation.loader.ini' => function () {
            return ${($_ = isset($this->services['translation.loader.ini']) ? $this->services['translation.loader.ini'] : ($this->services['translation.loader.ini'] = new \Symfony\Component\Translation\Loader\IniFileLoader())) && false ?: '_'};
        }, 'translation.loader.json' => function () {
            return ${($_ = isset($this->services['translation.loader.json']) ? $this->services['translation.loader.json'] : ($this->services['translation.loader.json'] = new \Symfony\Component\Translation\Loader\JsonFileLoader())) && false ?: '_'};
        }, 'translation.loader.mo' => function () {
            return ${($_ = isset($this->services['translation.loader.mo']) ? $this->services['translation.loader.mo'] : ($this->services['translation.loader.mo'] = new \Symfony\Component\Translation\Loader\MoFileLoader())) && false ?: '_'};
        }, 'translation.loader.php' => function () {
            return ${($_ = isset($this->services['translation.loader.php']) ? $this->services['translation.loader.php'] : ($this->services['translation.loader.php'] = new \Symfony\Component\Translation\Loader\PhpFileLoader())) && false ?: '_'};
        }, 'translation.loader.po' => function () {
            return ${($_ = isset($this->services['translation.loader.po']) ? $this->services['translation.loader.po'] : ($this->services['translation.loader.po'] = new \Symfony\Component\Translation\Loader\PoFileLoader())) && false ?: '_'};
        }, 'translation.loader.qt' => function () {
            return ${($_ = isset($this->services['translation.loader.qt']) ? $this->services['translation.loader.qt'] : ($this->services['translation.loader.qt'] = new \Symfony\Component\Translation\Loader\QtFileLoader())) && false ?: '_'};
        }, 'translation.loader.res' => function () {
            return ${($_ = isset($this->services['translation.loader.res']) ? $this->services['translation.loader.res'] : ($this->services['translation.loader.res'] = new \Symfony\Component\Translation\Loader\IcuResFileLoader())) && false ?: '_'};
        }, 'translation.loader.xliff' => function () {
            return ${($_ = isset($this->services['translation.loader.xliff']) ? $this->services['translation.loader.xliff'] : ($this->services['translation.loader.xliff'] = new \Symfony\Component\Translation\Loader\XliffFileLoader())) && false ?: '_'};
        }, 'translation.loader.yml' => function () {
            return ${($_ = isset($this->services['translation.loader.yml']) ? $this->services['translation.loader.yml'] : ($this->services['translation.loader.yml'] = new \Symfony\Component\Translation\Loader\YamlFileLoader())) && false ?: '_'};
        }]), new \Symfony\Component\Translation\Formatter\MessageFormatter(new \Symfony\Component\Translation\MessageSelector()), 'en', ['translation.loader.php' => [0 => 'php'], 'translation.loader.yml' => [0 => 'yaml', 1 => 'yml'], 'translation.loader.xliff' => [0 => 'xlf', 1 => 'xliff'], 'translation.loader.po' => [0 => 'po'], 'translation.loader.mo' => [0 => 'mo'], 'translation.loader.qt' => [0 => 'ts'], 'translation.loader.csv' => [0 => 'csv'], 'translation.loader.res' => [0 => 'res'], 'translation.loader.dat' => [0 => 'dat'], 'translation.loader.ini' => [0 => 'ini'], 'translation.loader.json' => [0 => 'json']], ['cache_dir' => ($this->targetDirs[0].'/translations'), 'debug' => true, 'resource_files' => ['af' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.af.xlf')], 'ar' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.ar.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.ar.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.ar.xlf'), 3 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.ar.xlf'), 4 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.ar.xlf')], 'az' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.az.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.az.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.az.xlf')], 'be' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.be.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.be.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.be.xlf')], 'bg' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.bg.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.bg.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.bg.xlf'), 3 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.bg.xlf'), 4 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.bg.xlf')], 'ca' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.ca.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.ca.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.ca.xlf'), 3 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.ca.xlf'), 4 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.ca.xlf')], 'cs' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.cs.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.cs.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.cs.xlf'), 3 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/CraueFormFlowBundle.cs.yml'), 4 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/validators.cs.yml'), 5 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.cs.xlf'), 6 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.cs.xlf')], 'cy' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.cy.xlf')], 'da' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.da.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.da.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.da.xlf'), 3 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.da.xlf'), 4 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.da.xlf')], 'de' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.de.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.de.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.de.xlf'), 3 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/CraueFormFlowBundle.de.yml'), 4 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/validators.de.yml'), 5 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.de.xlf'), 6 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.de.xlf')], 'el' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.el.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.el.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.el.xlf'), 3 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.el.xlf'), 4 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.el.xlf')], 'en' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.en.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.en.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.en.xlf'), 3 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/CraueFormFlowBundle.en.yml'), 4 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/validators.en.yml'), 5 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.en.xlf'), 6 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.en.xlf'), 7 => ($this->targetDirs[4].'/app/Resources/translations/form.en.yml'), 8 => ($this->targetDirs[4].'/app/Resources/translations/validators.en.yml')], 'es' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.es.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.es.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.es.xlf'), 3 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/CraueFormFlowBundle.es.yml'), 4 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/validators.es.yml'), 5 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.es.xlf'), 6 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.es.xlf')], 'et' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.et.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.et.xlf')], 'eu' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.eu.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.eu.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.eu.xlf'), 3 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.eu.xlf'), 4 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.eu.xlf')], 'fa' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.fa.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.fa.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.fa.xlf'), 3 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/CraueFormFlowBundle.fa.yml'), 4 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/validators.fa.yml'), 5 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.fa.xlf'), 6 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.fa.xlf')], 'fi' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.fi.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.fi.xlf'), 2 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.fi.xlf'), 3 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.fi.xlf')], 'fr' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.fr.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.fr.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.fr.xlf'), 3 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/CraueFormFlowBundle.fr.yml'), 4 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/validators.fr.yml'), 5 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.fr.xlf'), 6 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.fr.xlf')], 'gl' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.gl.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.gl.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.gl.xlf'), 3 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.gl.xlf'), 4 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.gl.xlf')], 'he' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.he.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.he.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.he.xlf')], 'hr' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.hr.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.hr.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.hr.xlf'), 3 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.hr.xlf'), 4 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.hr.xlf')], 'hu' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.hu.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.hu.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.hu.xlf'), 3 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/CraueFormFlowBundle.hu.yml'), 4 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/validators.hu.yml'), 5 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.hu.xlf'), 6 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.hu.xlf')], 'hy' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.hy.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.hy.xlf')], 'id' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.id.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.id.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.id.xlf')], 'it' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.it.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.it.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.it.xlf'), 3 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/CraueFormFlowBundle.it.yml'), 4 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/validators.it.yml'), 5 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.it.xlf'), 6 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.it.xlf')], 'ja' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.ja.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.ja.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.ja.xlf')], 'lb' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.lb.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.lb.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.lb.xlf')], 'lt' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.lt.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.lt.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.lt.xlf'), 3 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.lt.xlf'), 4 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.lt.xlf')], 'lv' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.lv.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.lv.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.lv.xlf')], 'mn' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.mn.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.mn.xlf')], 'nb' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.nb.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.nb.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.nb.xlf')], 'nl' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.nl.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.nl.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.nl.xlf'), 3 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/CraueFormFlowBundle.nl.yml'), 4 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/validators.nl.yml'), 5 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.nl.xlf'), 6 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.nl.xlf')], 'nn' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.nn.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.nn.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.nn.xlf')], 'no' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.no.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.no.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.no.xlf')], 'pl' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.pl.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.pl.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.pl.xlf'), 3 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/CraueFormFlowBundle.pl.yml'), 4 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/validators.pl.yml'), 5 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.pl.xlf'), 6 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.pl.xlf')], 'pt' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.pt.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.pt.xlf'), 2 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.pt.xlf'), 3 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.pt.xlf')], 'pt_BR' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.pt_BR.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.pt_BR.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.pt_BR.xlf'), 3 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/CraueFormFlowBundle.pt_BR.yml'), 4 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/validators.pt_BR.yml')], 'ro' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.ro.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.ro.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.ro.xlf'), 3 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.ro.xlf'), 4 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.ro.xlf')], 'ru' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.ru.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.ru.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.ru.xlf'), 3 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/CraueFormFlowBundle.ru.yml'), 4 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/validators.ru.yml'), 5 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.ru.xlf'), 6 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.ru.xlf')], 'sk' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.sk.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.sk.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.sk.xlf'), 3 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/CraueFormFlowBundle.sk.yml'), 4 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/validators.sk.yml')], 'sl' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.sl.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.sl.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.sl.xlf'), 3 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.sl.xlf'), 4 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.sl.xlf')], 'sq' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.sq.xlf')], 'sr_Cyrl' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.sr_Cyrl.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.sr_Cyrl.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.sr_Cyrl.xlf')], 'sr_Latn' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.sr_Latn.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.sr_Latn.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.sr_Latn.xlf')], 'sv' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.sv.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.sv.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.sv.xlf'), 3 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.sv.xlf'), 4 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.sv.xlf')], 'th' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.th.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.th.xlf')], 'tl' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.tl.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.tl.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.tl.xlf')], 'tr' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.tr.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.tr.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.tr.xlf'), 3 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.tr.xlf'), 4 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.tr.xlf')], 'uk' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.uk.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.uk.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.uk.xlf'), 3 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/CraueFormFlowBundle.uk.yml'), 4 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/validators.uk.yml'), 5 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.uk.xlf'), 6 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.uk.xlf')], 'vi' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.vi.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.vi.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.vi.xlf')], 'zh_CN' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.zh_CN.xlf'), 1 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.zh_CN.xlf'), 2 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.zh_CN.xlf'), 3 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/CraueFormFlowBundle.zh_CN.yml'), 4 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/validators.zh_CN.yml'), 5 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.zh_CN.xlf'), 6 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.zh_CN.xlf')], 'zh_TW' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.zh_TW.xlf'), 1 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/CraueFormFlowBundle.zh_TW.yml'), 2 => ($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/translations/validators.zh_TW.yml')], 'pt_PT' => [0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Resources/translations/security.pt_PT.xlf')], 'sr_RS' => [0 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.sr_RS.xlf'), 1 => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/translations/messages.sr_RS.xlf')]]]);

        $instance->setConfigCacheFactory(${($_ = isset($this->services['config_cache_factory']) ? $this->services['config_cache_factory'] : $this->getConfigCacheFactoryService()) && false ?: '_'});
        $instance->setFallbackLocales([0 => 'en']);

        return $instance;
    }

    /**
     * Gets the private 'translator_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\TranslatorListener
     */
    protected function getTranslatorListenerService()
    {
        return $this->services['translator_listener'] = new \Symfony\Component\HttpKernel\EventListener\TranslatorListener(${($_ = isset($this->services['translator']) ? $this->services['translator'] : $this->getTranslatorService()) && false ?: '_'}, ${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : ($this->services['request_stack'] = new \Symfony\Component\HttpFoundation\RequestStack())) && false ?: '_'});
    }

    /**
     * Gets the private 'twig.extension.craue_formflow' shared service.
     *
     * @return \Craue\FormFlowBundle\Twig\Extension\FormFlowExtension
     */
    protected function getTwig_Extension_CraueFormflowService()
    {
        $this->services['twig.extension.craue_formflow'] = $instance = new \Craue\FormFlowBundle\Twig\Extension\FormFlowExtension();

        $instance->setFormFlowUtil(${($_ = isset($this->services['craue_formflow_util']) ? $this->services['craue_formflow_util'] : ($this->services['craue_formflow_util'] = new \Craue\FormFlowBundle\Util\FormFlowUtil())) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the private 'twig.extension.routing' shared service.
     *
     * @return \Symfony\Bridge\Twig\Extension\RoutingExtension
     */
    protected function getTwig_Extension_RoutingService()
    {
        return $this->services['twig.extension.routing'] = new \Symfony\Bridge\Twig\Extension\RoutingExtension(${($_ = isset($this->services['Symfony\\Component\\Routing\\RouterInterface']) ? $this->services['Symfony\\Component\\Routing\\RouterInterface'] : $this->getRouterInterfaceService()) && false ?: '_'});
    }

    /**
     * Gets the private 'twig.loader' shared service.
     *
     * @return \Twig\Loader\ChainLoader
     */
    protected function getTwig_LoaderService()
    {
        $this->services['twig.loader'] = $instance = new \Twig\Loader\ChainLoader();

        $instance->addLoader(${($_ = isset($this->services['Rialto\\Cms\\CmsLoader']) ? $this->services['Rialto\\Cms\\CmsLoader'] : $this->getCmsLoaderService()) && false ?: '_'});
        $instance->addLoader(${($_ = isset($this->services['twig.loader.filesystem']) ? $this->services['twig.loader.filesystem'] : $this->getTwig_Loader_FilesystemService()) && false ?: '_'});

        return $instance;
    }

    /**
     * Gets the private 'twig.loader.filesystem' shared service.
     *
     * @return \Symfony\Bundle\TwigBundle\Loader\FilesystemLoader
     */
    protected function getTwig_Loader_FilesystemService()
    {
        $this->services['twig.loader.filesystem'] = $instance = new \Symfony\Bundle\TwigBundle\Loader\FilesystemLoader(${($_ = isset($this->services['templating.locator']) ? $this->services['templating.locator'] : $this->getTemplating_LocatorService()) && false ?: '_'}, ${($_ = isset($this->services['templating.name_parser']) ? $this->services['templating.name_parser'] : ($this->services['templating.name_parser'] = new \Symfony\Bundle\FrameworkBundle\Templating\TemplateNameParser(${($_ = isset($this->services['kernel']) ? $this->services['kernel'] : $this->get('kernel', 1)) && false ?: '_'}))) && false ?: '_'}, $this->targetDirs[4]);

        $instance->addPath(($this->targetDirs[4].'/app/../templates'));
        $instance->addPath(($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views'), 'Framework');
        $instance->addPath(($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views'), '!Framework');
        $instance->addPath(($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Bundle/SecurityBundle/Resources/views'), 'Security');
        $instance->addPath(($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Bundle/SecurityBundle/Resources/views'), '!Security');
        $instance->addPath(($this->targetDirs[4].'/app/Resources/TwigBundle/views'), 'Twig');
        $instance->addPath(($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Bundle/TwigBundle/Resources/views'), 'Twig');
        $instance->addPath(($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Bundle/TwigBundle/Resources/views'), '!Twig');
        $instance->addPath(($this->targetDirs[4].'/vendor/symfony/swiftmailer-bundle/Resources/views'), 'Swiftmailer');
        $instance->addPath(($this->targetDirs[4].'/vendor/symfony/swiftmailer-bundle/Resources/views'), '!Swiftmailer');
        $instance->addPath(($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/views'), 'CraueFormFlow');
        $instance->addPath(($this->targetDirs[4].'/vendor/craue/formflow-bundle/Resources/views'), '!CraueFormFlow');
        $instance->addPath(($this->targetDirs[4].'/vendor/doctrine/doctrine-bundle/Resources/views'), 'Doctrine');
        $instance->addPath(($this->targetDirs[4].'/vendor/doctrine/doctrine-bundle/Resources/views'), '!Doctrine');
        $instance->addPath(($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/views'), 'EasyAdmin');
        $instance->addPath(($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src/Resources/views'), '!EasyAdmin');
        $instance->addPath(($this->targetDirs[4].'/vendor/jms/job-queue-bundle/JMS/JobQueueBundle/Resources/views'), 'JMSJobQueue');
        $instance->addPath(($this->targetDirs[4].'/vendor/jms/job-queue-bundle/JMS/JobQueueBundle/Resources/views'), '!JMSJobQueue');
        $instance->addPath(($this->targetDirs[4].'/vendor/gumstix/form-bundle/src/Resources/views'), 'GumstixForm');
        $instance->addPath(($this->targetDirs[4].'/vendor/gumstix/form-bundle/src/Resources/views'), '!GumstixForm');
        $instance->addPath(($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Bundle/WebProfilerBundle/Resources/views'), 'WebProfiler');
        $instance->addPath(($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Bundle/WebProfilerBundle/Resources/views'), '!WebProfiler');
        $instance->addPath(($this->targetDirs[4].'/templates'));
        $instance->addPath(($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Bridge/Twig/Resources/views/Form'));

        return $instance;
    }

    /**
     * Gets the private 'twig.profile' shared service.
     *
     * @return \Twig\Profiler\Profile
     */
    protected function getTwig_ProfileService()
    {
        return $this->services['twig.profile'] = new \Twig\Profiler\Profile();
    }

    /**
     * Gets the private 'validate_request_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\ValidateRequestListener
     */
    protected function getValidateRequestListenerService()
    {
        return $this->services['validate_request_listener'] = new \Symfony\Component\HttpKernel\EventListener\ValidateRequestListener();
    }

    /**
     * Gets the private 'validator.builder' shared service.
     *
     * @return \Symfony\Component\Validator\ValidatorBuilderInterface
     */
    protected function getValidator_BuilderService()
    {
        $this->services['validator.builder'] = $instance = \Symfony\Component\Validator\Validation::createValidatorBuilder();

        $instance->setConstraintValidatorFactory(new \Symfony\Component\Validator\ContainerConstraintValidatorFactory(new \Symfony\Component\DependencyInjection\ServiceLocator(['Symfony\\Bridge\\Doctrine\\Validator\\Constraints\\UniqueEntityValidator' => function () {
            return ${($_ = isset($this->services['doctrine.orm.validator.unique']) ? $this->services['doctrine.orm.validator.unique'] : $this->load('getDoctrine_Orm_Validator_UniqueService.php')) && false ?: '_'};
        }, 'Rialto\\Accounting\\Bank\\Account\\AvailableChequeNumberValidator' => function () {
            return ${($_ = isset($this->services['Rialto\\Accounting\\Bank\\Account\\AvailableChequeNumberValidator']) ? $this->services['Rialto\\Accounting\\Bank\\Account\\AvailableChequeNumberValidator'] : $this->load('getAvailableChequeNumberValidatorService.php')) && false ?: '_'};
        }, 'Rialto\\Allocation\\Validator\\PurchasingDataExistsForChildValidator' => function () {
            return ${($_ = isset($this->services['Rialto\\Allocation\\Validator\\PurchasingDataExistsForChildValidator']) ? $this->services['Rialto\\Allocation\\Validator\\PurchasingDataExistsForChildValidator'] : $this->load('getPurchasingDataExistsForChildValidatorService.php')) && false ?: '_'};
        }, 'Rialto\\Allocation\\Validator\\PurchasingDataExistsValidator' => function () {
            return ${($_ = isset($this->services['Rialto\\Allocation\\Validator\\PurchasingDataExistsValidator']) ? $this->services['Rialto\\Allocation\\Validator\\PurchasingDataExistsValidator'] : $this->load('getPurchasingDataExistsValidatorService.php')) && false ?: '_'};
        }, 'Rialto\\Manufacturing\\Bom\\Validator\\IsValidBomCsvValidator' => function () {
            return ${($_ = isset($this->services['Rialto\\Manufacturing\\Bom\\Validator\\IsValidBomCsvValidator']) ? $this->services['Rialto\\Manufacturing\\Bom\\Validator\\IsValidBomCsvValidator'] : $this->load('getIsValidBomCsvValidatorService.php')) && false ?: '_'};
        }, 'Rialto\\Panelization\\Validator\\PurchasingDataExistsValidator' => function () {
            return ${($_ = isset($this->services['Rialto\\Panelization\\Validator\\PurchasingDataExistsValidator']) ? $this->services['Rialto\\Panelization\\Validator\\PurchasingDataExistsValidator'] : $this->load('getPurchasingDataExistsValidator2Service.php')) && false ?: '_'};
        }, 'Rialto\\Purchasing\\Receiving\\Auth\\CanReceiveIntoValidator' => function () {
            return ${($_ = isset($this->services['Rialto\\Purchasing\\Receiving\\Auth\\CanReceiveIntoValidator']) ? $this->services['Rialto\\Purchasing\\Receiving\\Auth\\CanReceiveIntoValidator'] : $this->load('getCanReceiveIntoValidatorService.php')) && false ?: '_'};
        }, 'Rialto\\Shipping\\Export\\AllowedCountryValidator' => function () {
            return ${($_ = isset($this->services['Rialto\\Shipping\\Export\\AllowedCountryValidator']) ? $this->services['Rialto\\Shipping\\Export\\AllowedCountryValidator'] : $this->load('getAllowedCountryValidatorService.php')) && false ?: '_'};
        }, 'Rialto\\Stock\\Item\\NewSkuValidator' => function () {
            return ${($_ = isset($this->services['Rialto\\Stock\\Item\\NewSkuValidator']) ? $this->services['Rialto\\Stock\\Item\\NewSkuValidator'] : $this->load('getNewSkuValidatorService.php')) && false ?: '_'};
        }, 'Symfony\\Component\\Security\\Core\\Validator\\Constraints\\UserPasswordValidator' => function () {
            return ${($_ = isset($this->services['security.validator.user_password']) ? $this->services['security.validator.user_password'] : $this->load('getSecurity_Validator_UserPasswordService.php')) && false ?: '_'};
        }, 'Symfony\\Component\\Validator\\Constraints\\EmailValidator' => function () {
            return ${($_ = isset($this->services['validator.email']) ? $this->services['validator.email'] : ($this->services['validator.email'] = new \Symfony\Component\Validator\Constraints\EmailValidator(false))) && false ?: '_'};
        }, 'Symfony\\Component\\Validator\\Constraints\\ExpressionValidator' => function () {
            return ${($_ = isset($this->services['validator.expression']) ? $this->services['validator.expression'] : ($this->services['validator.expression'] = new \Symfony\Component\Validator\Constraints\ExpressionValidator())) && false ?: '_'};
        }, 'doctrine.orm.validator.unique' => function () {
            return ${($_ = isset($this->services['doctrine.orm.validator.unique']) ? $this->services['doctrine.orm.validator.unique'] : $this->load('getDoctrine_Orm_Validator_UniqueService.php')) && false ?: '_'};
        }, 'security.validator.user_password' => function () {
            return ${($_ = isset($this->services['security.validator.user_password']) ? $this->services['security.validator.user_password'] : $this->load('getSecurity_Validator_UserPasswordService.php')) && false ?: '_'};
        }, 'validator.expression' => function () {
            return ${($_ = isset($this->services['validator.expression']) ? $this->services['validator.expression'] : ($this->services['validator.expression'] = new \Symfony\Component\Validator\Constraints\ExpressionValidator())) && false ?: '_'};
        }])));
        if ($this->has('translator')) {
            $instance->setTranslator(${($_ = isset($this->services['translator']) ? $this->services['translator'] : $this->getTranslatorService()) && false ?: '_'});
        }
        $instance->setTranslationDomain('validators');
        $instance->addXmlMappings([0 => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/config/validation.xml')]);
        $instance->enableAnnotationMapping(${($_ = isset($this->services['annotation_reader']) ? $this->services['annotation_reader'] : $this->getAnnotationReaderService()) && false ?: '_'});
        $instance->addMethodMapping('loadValidatorMetadata');
        $instance->addObjectInitializers([0 => ${($_ = isset($this->services['doctrine.orm.validator_initializer']) ? $this->services['doctrine.orm.validator_initializer'] : $this->getDoctrine_Orm_ValidatorInitializerService()) && false ?: '_'}]);

        return $instance;
    }

    /**
     * Gets the private 'web_profiler.csp.handler' shared service.
     *
     * @return \Symfony\Bundle\WebProfilerBundle\Csp\ContentSecurityPolicyHandler
     */
    protected function getWebProfiler_Csp_HandlerService()
    {
        return $this->services['web_profiler.csp.handler'] = new \Symfony\Bundle\WebProfilerBundle\Csp\ContentSecurityPolicyHandler(new \Symfony\Bundle\WebProfilerBundle\Csp\NonceGenerator());
    }

    /**
     * Gets the private 'web_profiler.debug_toolbar' shared service.
     *
     * @return \Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener
     */
    protected function getWebProfiler_DebugToolbarService()
    {
        return $this->services['web_profiler.debug_toolbar'] = new \Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener(${($_ = isset($this->services['twig']) ? $this->services['twig'] : $this->getTwigService()) && false ?: '_'}, false, 2, 'bottom', ${($_ = isset($this->services['Symfony\\Component\\Routing\\RouterInterface']) ? $this->services['Symfony\\Component\\Routing\\RouterInterface'] : $this->getRouterInterfaceService()) && false ?: '_'}, '^/((index|app(_[\\w]+)?)\\.php/)?_wdt', ${($_ = isset($this->services['web_profiler.csp.handler']) ? $this->services['web_profiler.csp.handler'] : $this->getWebProfiler_Csp_HandlerService()) && false ?: '_'});
    }

    public function getParameter($name)
    {
        $name = (string) $name;
        if (isset($this->buildParameters[$name])) {
            return $this->buildParameters[$name];
        }
        if (!(isset($this->parameters[$name]) || isset($this->loadedDynamicParameters[$name]) || array_key_exists($name, $this->parameters))) {
            $name = $this->normalizeParameterName($name);

            if (!(isset($this->parameters[$name]) || isset($this->loadedDynamicParameters[$name]) || array_key_exists($name, $this->parameters))) {
                throw new InvalidArgumentException(sprintf('The parameter "%s" must be defined.', $name));
            }
        }
        if (isset($this->loadedDynamicParameters[$name])) {
            return $this->loadedDynamicParameters[$name] ? $this->dynamicParameters[$name] : $this->getDynamicParameter($name);
        }

        return $this->parameters[$name];
    }

    public function hasParameter($name)
    {
        $name = (string) $name;
        if (isset($this->buildParameters[$name])) {
            return true;
        }
        $name = $this->normalizeParameterName($name);

        return isset($this->parameters[$name]) || isset($this->loadedDynamicParameters[$name]) || array_key_exists($name, $this->parameters);
    }

    public function setParameter($name, $value)
    {
        throw new LogicException('Impossible to call set() on a frozen ParameterBag.');
    }

    public function getParameterBag()
    {
        if (null === $this->parameterBag) {
            $parameters = $this->parameters;
            foreach ($this->loadedDynamicParameters as $name => $loaded) {
                $parameters[$name] = $loaded ? $this->dynamicParameters[$name] : $this->getDynamicParameter($name);
            }
            foreach ($this->buildParameters as $name => $value) {
                $parameters[$name] = $value;
            }
            $this->parameterBag = new FrozenParameterBag($parameters);
        }

        return $this->parameterBag;
    }

    private $loadedDynamicParameters = [
        'kernel.root_dir' => false,
        'kernel.project_dir' => false,
        'kernel.cache_dir' => false,
        'kernel.logs_dir' => false,
        'kernel.bundles_metadata' => false,
        'rialto_util.font_dir' => false,
        'rialto.root_dir' => false,
        'session.save_path' => false,
        'validator.mapping.cache.file' => false,
        'translator.default_path' => false,
        'debug.container.dump' => false,
        'router.resource' => false,
        'serializer.mapping.cache.file' => false,
        'twig.default_path' => false,
        'doctrine.orm.proxy_dir' => false,
        'doctrine_migrations.dir_name' => false,
        'easyadmin.cache.dir' => false,
        'gumstix_sso.credential_file' => false,
    ];
    private $dynamicParameters = [];

    /**
     * Computes a dynamic parameter.
     *
     * @param string $name The name of the dynamic parameter to load
     *
     * @return mixed The value of the dynamic parameter
     *
     * @throws InvalidArgumentException When the dynamic parameter does not exist
     */
    private function getDynamicParameter($name)
    {
        switch ($name) {
            case 'kernel.root_dir': $value = ($this->targetDirs[4].'/app'); break;
            case 'kernel.project_dir': $value = $this->targetDirs[4]; break;
            case 'kernel.cache_dir': $value = $this->targetDirs[0]; break;
            case 'kernel.logs_dir': $value = ($this->targetDirs[3].'/logs/www-data'); break;
            case 'kernel.bundles_metadata': $value = [
                'FrameworkBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle'),
                    'namespace' => 'Symfony\\Bundle\\FrameworkBundle',
                ],
                'SecurityBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Bundle/SecurityBundle'),
                    'namespace' => 'Symfony\\Bundle\\SecurityBundle',
                ],
                'TwigBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Bundle/TwigBundle'),
                    'namespace' => 'Symfony\\Bundle\\TwigBundle',
                ],
                'MonologBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/vendor/symfony/monolog-bundle'),
                    'namespace' => 'Symfony\\Bundle\\MonologBundle',
                ],
                'SwiftmailerBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/vendor/symfony/swiftmailer-bundle'),
                    'namespace' => 'Symfony\\Bundle\\SwiftmailerBundle',
                ],
                'CraueFormFlowBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/vendor/craue/formflow-bundle'),
                    'namespace' => 'Craue\\FormFlowBundle',
                ],
                'DoctrineBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/vendor/doctrine/doctrine-bundle'),
                    'namespace' => 'Doctrine\\Bundle\\DoctrineBundle',
                ],
                'DoctrineMigrationsBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/vendor/doctrine/doctrine-migrations-bundle'),
                    'namespace' => 'Doctrine\\Bundle\\MigrationsBundle',
                ],
                'SensioFrameworkExtraBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/vendor/sensio/framework-extra-bundle'),
                    'namespace' => 'Sensio\\Bundle\\FrameworkExtraBundle',
                ],
                'EasyAdminBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/vendor/easycorp/easyadmin-bundle/src'),
                    'namespace' => 'EasyCorp\\Bundle\\EasyAdminBundle',
                ],
                'JMSJobQueueBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/vendor/jms/job-queue-bundle/JMS/JobQueueBundle'),
                    'namespace' => 'JMS\\JobQueueBundle',
                ],
                'JMSSerializerBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/vendor/jms/serializer-bundle'),
                    'namespace' => 'JMS\\SerializerBundle',
                ],
                'FOSRestBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/vendor/friendsofsymfony/rest-bundle'),
                    'namespace' => 'FOS\\RestBundle',
                ],
                'FOSJsRoutingBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/vendor/friendsofsymfony/jsrouting-bundle'),
                    'namespace' => 'FOS\\JsRoutingBundle',
                ],
                'NelmioSecurityBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/vendor/nelmio/security-bundle'),
                    'namespace' => 'Nelmio\\SecurityBundle',
                ],
                'NelmioCorsBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/vendor/nelmio/cors-bundle'),
                    'namespace' => 'Nelmio\\CorsBundle',
                ],
                'TacticianBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/vendor/league/tactician-bundle/src'),
                    'namespace' => 'League\\Tactician\\Bundle',
                ],
                'GumstixFormBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/vendor/gumstix/form-bundle/src'),
                    'namespace' => 'Gumstix\\FormBundle',
                ],
                'GumstixGeographyBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/vendor/gumstix/geo-bundle/src'),
                    'namespace' => 'Gumstix\\GeographyBundle',
                ],
                'GumstixRestBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/vendor/gumstix/rest-bundle/src'),
                    'namespace' => 'Gumstix\\RestBundle',
                ],
                'GumstixSSOBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/vendor/gumstix/sso-bundle/src/SSOBundle'),
                    'namespace' => 'Gumstix\\SSOBundle',
                ],
                'WebpackEncoreBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/vendor/symfony/webpack-encore-bundle/src'),
                    'namespace' => 'Symfony\\WebpackEncoreBundle',
                ],
                'WebProfilerBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/vendor/symfony/symfony/src/Symfony/Bundle/WebProfilerBundle'),
                    'namespace' => 'Symfony\\Bundle\\WebProfilerBundle',
                ],
                'GumstixTestingBundle' => [
                    'parent' => NULL,
                    'path' => ($this->targetDirs[4].'/src/Gumstix/TestingBundle'),
                    'namespace' => 'Gumstix\\TestingBundle',
                ],
            ]; break;
            case 'rialto_util.font_dir': $value = ($this->targetDirs[4].'/fonts'); break;
            case 'rialto.root_dir': $value = ($this->targetDirs[4].'/app/..'); break;
            case 'session.save_path': $value = ($this->targetDirs[0].'/sessions'); break;
            case 'validator.mapping.cache.file': $value = ($this->targetDirs[0].'/validation.php'); break;
            case 'translator.default_path': $value = ($this->targetDirs[4].'/translations'); break;
            case 'debug.container.dump': $value = ($this->targetDirs[0].'/appDevDebugProjectContainer.xml'); break;
            case 'router.resource': $value = ($this->targetDirs[4].'/app/config/routing_dev.yaml'); break;
            case 'serializer.mapping.cache.file': $value = ($this->targetDirs[0].'/serialization.php'); break;
            case 'twig.default_path': $value = ($this->targetDirs[4].'/templates'); break;
            case 'doctrine.orm.proxy_dir': $value = ($this->targetDirs[0].'/doctrine/orm/Proxies'); break;
            case 'doctrine_migrations.dir_name': $value = ($this->targetDirs[4].'/app/migrations'); break;
            case 'easyadmin.cache.dir': $value = ($this->targetDirs[0].'/easy_admin'); break;
            case 'gumstix_sso.credential_file': $value = ($this->targetDirs[4].'/app/../var/data/gumstix_sso/credential.data'); break;
            default: throw new InvalidArgumentException(sprintf('The dynamic parameter "%s" must be defined.', $name));
        }
        $this->loadedDynamicParameters[$name] = true;

        return $this->dynamicParameters[$name] = $value;
    }

    private $normalizedParameterNames = [];

    private function normalizeParameterName($name)
    {
        if (isset($this->normalizedParameterNames[$normalizedName = strtolower($name)]) || isset($this->parameters[$normalizedName]) || array_key_exists($normalizedName, $this->parameters)) {
            $normalizedName = isset($this->normalizedParameterNames[$normalizedName]) ? $this->normalizedParameterNames[$normalizedName] : $normalizedName;
            if ((string) $name !== $normalizedName) {
                @trigger_error(sprintf('Parameter names will be made case sensitive in Symfony 4.0. Using "%s" instead of "%s" is deprecated since Symfony 3.4.', $name, $normalizedName), E_USER_DEPRECATED);
            }
        } else {
            $normalizedName = $this->normalizedParameterNames[$normalizedName] = (string) $name;
        }

        return $normalizedName;
    }

    /**
     * Gets the default parameters.
     *
     * @return array An array of the default parameters
     */
    protected function getDefaultParameters()
    {
        return [
            'kernel.environment' => 'dev',
            'kernel.debug' => true,
            'kernel.name' => 'app',
            'kernel.bundles' => [
                'FrameworkBundle' => 'Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle',
                'SecurityBundle' => 'Symfony\\Bundle\\SecurityBundle\\SecurityBundle',
                'TwigBundle' => 'Symfony\\Bundle\\TwigBundle\\TwigBundle',
                'MonologBundle' => 'Symfony\\Bundle\\MonologBundle\\MonologBundle',
                'SwiftmailerBundle' => 'Symfony\\Bundle\\SwiftmailerBundle\\SwiftmailerBundle',
                'CraueFormFlowBundle' => 'Craue\\FormFlowBundle\\CraueFormFlowBundle',
                'DoctrineBundle' => 'Doctrine\\Bundle\\DoctrineBundle\\DoctrineBundle',
                'DoctrineMigrationsBundle' => 'Doctrine\\Bundle\\MigrationsBundle\\DoctrineMigrationsBundle',
                'SensioFrameworkExtraBundle' => 'Sensio\\Bundle\\FrameworkExtraBundle\\SensioFrameworkExtraBundle',
                'EasyAdminBundle' => 'EasyCorp\\Bundle\\EasyAdminBundle\\EasyAdminBundle',
                'JMSJobQueueBundle' => 'JMS\\JobQueueBundle\\JMSJobQueueBundle',
                'JMSSerializerBundle' => 'JMS\\SerializerBundle\\JMSSerializerBundle',
                'FOSRestBundle' => 'FOS\\RestBundle\\FOSRestBundle',
                'FOSJsRoutingBundle' => 'FOS\\JsRoutingBundle\\FOSJsRoutingBundle',
                'NelmioSecurityBundle' => 'Nelmio\\SecurityBundle\\NelmioSecurityBundle',
                'NelmioCorsBundle' => 'Nelmio\\CorsBundle\\NelmioCorsBundle',
                'TacticianBundle' => 'League\\Tactician\\Bundle\\TacticianBundle',
                'GumstixFormBundle' => 'Gumstix\\FormBundle\\GumstixFormBundle',
                'GumstixGeographyBundle' => 'Gumstix\\GeographyBundle\\GumstixGeographyBundle',
                'GumstixRestBundle' => 'Gumstix\\RestBundle\\GumstixRestBundle',
                'GumstixSSOBundle' => 'Gumstix\\SSOBundle\\GumstixSSOBundle',
                'WebpackEncoreBundle' => 'Symfony\\WebpackEncoreBundle\\WebpackEncoreBundle',
                'WebProfilerBundle' => 'Symfony\\Bundle\\WebProfilerBundle\\WebProfilerBundle',
                'GumstixTestingBundle' => 'Gumstix\\TestingBundle\\GumstixTestingBundle',
            ],
            'kernel.charset' => 'UTF-8',
            'kernel.container_class' => 'appDevDebugProjectContainer',
            'assets_version' => '2a685dc7e',
            'authorizenet.trans_key' => '',
            'ciiva_apikey' => '',
            'ciiva_password' => '',
            'db_password' => 'rialto',
            'email.password' => '',
            'enable_profiler' => false,
            'pcb_ng.api.password' => '03500208Gs',
            'pcb_ng.api.user' => 'stefan.zhang@gumstix.com',
            'rialto_email.xmpp_password' => '',
            'rialto_purchasing.octopart_catalog_apikey' => '',
            'rialto_purchasing.supplier_mailbox_password' => '',
            'rialto_wordpress.password' => '',
            'secret' => 'dbe2ad5133a4f5cc31bf35299e956df963ff944bb385d89e9b2b078d902c2816',
            'sentry_dsn' => '',
            'taxjar_api_token' => '',
            'ups.access_license' => '7BCE6A23028BC75C',
            'ups.invoice_password' => '',
            'ups.password' => 'saywhat',
            'fedex.key' => 'nn5npVFwcV3i7sFf',
            'fedex.password' => 'ZnvePMF2ZNJWUL5Eq92eoYTnR',
            'fedex.account_number' => '510087780',
            'fedex.meter_number' => '119167837',
            'rialto_purchasing.supplier_mailbox_server' => 'imap.gmail.com',
            'rialto_purchasing.supplier_mailbox_username' => 'engine@altium.com',
            'default_po_owner' => 'andrew.simpson',
            'rialto.sales.email.bcc' => 'jack@gumstix.com',
            'ups.denied_party.enabled' => 1,
            'ups.denied_party.screen_type' => 'Party',
            'ups.denied_party.match_level' => 'High',
            'ups.supply_chain_id' => 108,
            'debug.error_handler.throw_at' => -1,
            'db_driver' => 'pdo_mysql',
            'db_name' => 'rialto',
            'db_user' => 'rialto',
            'bugtracker.uri' => 'https://mantis.gumstix.com',
            'bugtracker.default_category' => 1,
            'bugtracker.project_id' => 1,
            'rialto_wordpress.username' => 'rialto',
            'rialto_wordpress.post_type' => 'pcn',
            'locale' => 'en',
            'aws_region' => 'us-west-2',
            'db_host' => '127.0.0.1',
            'mongo_host' => '127.0.0.1',
            'data_bucket' => 'devstix-rialto-files',
            'router.request_context.host' => 'rialto.mystix.com',
            'router.request_context.scheme' => 'http',
            'router.request_context.base_url' => '/index.php',
            'trusted_hosts' => [
                0 => 'mystix.com',
                1 => 'https://dev-storefront.pcbng.com/',
            ],
            'cors_hosts' => [
                0 => '^http://(.+.)?mystix.com',
            ],
            'use_ssl' => false,
            'user.admin' => 'ianfp',
            'user.webmaster' => 'ianfp',
            'email.transport' => 'smtp',
            'email.host' => 'email-smtp.us-west-2.amazonaws.com',
            'email.port' => 587,
            'email.encryption' => 'tls',
            'email.username' => 'AKIAI5WM7Y2VUODDW23Q',
            'ups.webservices.uri' => 'https://wwwcie.ups.com',
            'ups.user_id' => 'craighughes',
            'ups.invoice_host' => 'ftp2.ups.com',
            'ups.invoice_username' => 'gumstix0316',
            'authorizenet.login' => '6e79LeRw',
            'authorizenet.sandbox' => 1,
            'pcb_ng.storefront.base_uri' => 'https://dev-storefront.pcbng.com/',
            'pcb_ng.api.base_uri' => 'https://api-dev.pcbng.com/api/',
            'gumstix_sso.service.server' => 'http://accounts.mystix.com/',
            'rialto_wordpress.base_url' => 'http://www.mystix.com',
            'rialto_madison.madison_url' => 'http://madison.mystix.com/app.php',
            'catalina.base_url' => 'http://catalina.mystix.com',
            'geppetto.base_url' => 'http://geppetto.mystix.com',
            'fragment.renderer.hinclude.global_template' => NULL,
            'fragment.path' => '/_fragment',
            'kernel.secret' => 'dbe2ad5133a4f5cc31bf35299e956df963ff944bb385d89e9b2b078d902c2816',
            'kernel.http_method_override' => true,
            'kernel.trusted_hosts' => [
                0 => 'mystix.com',
                1 => 'https://dev-storefront.pcbng.com/',
            ],
            'kernel.default_locale' => 'en',
            'templating.helper.code.file_link_format' => NULL,
            'debug.file_link_format' => NULL,
            'session.metadata.storage_key' => '_sf2_meta',
            'session.storage.options' => [
                'cache_limiter' => '0',
                'cookie_secure' => false,
                'cookie_httponly' => true,
                'gc_probability' => 1,
                'use_strict_mode' => true,
            ],
            'session.metadata.update_threshold' => '0',
            'form.type_extension.csrf.enabled' => true,
            'form.type_extension.csrf.field_name' => '_token',
            'asset.request_context.base_path' => '',
            'asset.request_context.secure' => false,
            'templating.loader.cache.path' => NULL,
            'templating.engines' => [
                0 => 'twig',
            ],
            'validator.mapping.cache.prefix' => '',
            'validator.translation_domain' => 'validators',
            'translator.logging' => true,
            'data_collector.templates' => [

            ],
            'router.options.generator_class' => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator',
            'router.options.generator_base_class' => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator',
            'router.options.generator_dumper_class' => 'Symfony\\Component\\Routing\\Generator\\Dumper\\PhpGeneratorDumper',
            'router.options.matcher_class' => 'Symfony\\Bundle\\FrameworkBundle\\Routing\\RedirectableUrlMatcher',
            'router.options.matcher_base_class' => 'Symfony\\Bundle\\FrameworkBundle\\Routing\\RedirectableUrlMatcher',
            'router.options.matcher_dumper_class' => 'Symfony\\Component\\Routing\\Matcher\\Dumper\\PhpMatcherDumper',
            'router.options.matcher.cache_class' => 'appDevDebugProjectContainerUrlMatcher',
            'router.options.generator.cache_class' => 'appDevDebugProjectContainerUrlGenerator',
            'router.cache_class_prefix' => 'appDevDebugProjectContainer',
            'request_listener.http_port' => 80,
            'request_listener.https_port' => 443,
            'serializer.mapping.cache.prefix' => '',
            'security.authentication.trust_resolver.anonymous_class' => 'Symfony\\Component\\Security\\Core\\Authentication\\Token\\AnonymousToken',
            'security.authentication.trust_resolver.rememberme_class' => 'Symfony\\Component\\Security\\Core\\Authentication\\Token\\RememberMeToken',
            'security.role_hierarchy.roles' => [
                'ROLE_ADMIN' => [
                    0 => 'ROLE_ACCOUNTING',
                    1 => 'ROLE_ENGINEER',
                    2 => 'ROLE_MANUFACTURING',
                    3 => 'ROLE_PURCHASING',
                    4 => 'ROLE_RECEIVING',
                    5 => 'ROLE_SALES',
                    6 => 'ROLE_SHIPPING',
                    7 => 'ROLE_STOCK_CREATE',
                    8 => 'ROLE_WAREHOUSE',
                ],
                'ROLE_ENGINEER' => [
                    0 => 'ROLE_STOCK',
                    1 => 'ROLE_MANUFACTURING',
                    2 => 'ROLE_PURCHASING',
                ],
                'ROLE_WAREHOUSE' => [
                    0 => 'ROLE_STOCK',
                    1 => 'ROLE_RECEIVING',
                    2 => 'ROLE_SHIPPING',
                ],
                'ROLE_SALES' => [
                    0 => 'ROLE_CUSTOMER_SERVICE',
                ],
                'ROLE_ACCOUNTING' => [
                    0 => 'ROLE_EMPLOYEE',
                ],
                'ROLE_CUSTOMER_SERVICE' => [
                    0 => 'ROLE_STOCK_VIEW',
                ],
                'ROLE_MANUFACTURING' => [
                    0 => 'ROLE_EMPLOYEE',
                ],
                'ROLE_PURCHASING' => [
                    0 => 'ROLE_PURCHASING_DATA',
                ],
                'ROLE_PURCHASING_DATA' => [
                    0 => 'ROLE_STOCK_VIEW',
                ],
                'ROLE_RECEIVING' => [
                    0 => 'ROLE_EMPLOYEE',
                ],
                'ROLE_SHIPPING' => [
                    0 => 'ROLE_EMPLOYEE',
                ],
                'ROLE_STOCK_CREATE' => [
                    0 => 'ROLE_STOCK',
                ],
                'ROLE_STOCK' => [
                    0 => 'ROLE_STOCK_VIEW',
                ],
                'ROLE_STOCK_VIEW' => [
                    0 => 'ROLE_EMPLOYEE',
                ],
                'ROLE_STOREFRONT' => [
                    0 => 'ROLE_API_CLIENT',
                ],
                'ROLE_SUPPLIER_ADVANCED' => [
                    0 => 'ROLE_SUPPLIER_SIMPLE',
                ],
            ],
            'security.access.denied_url' => NULL,
            'security.authentication.manager.erase_credentials' => true,
            'security.authentication.session_strategy.strategy' => 'migrate',
            'security.access.always_authenticate_before_granting' => false,
            'security.authentication.hide_user_not_found' => true,
            'twig.exception_listener.controller' => 'FOS\\RestBundle\\Controller\\ExceptionController::showAction',
            'twig.form.resources' => [
                0 => 'form_div_layout.html.twig',
                1 => 'GumstixFormBundle:Form:fields.html.twig',
                2 => 'form/fields.html.twig',
                3 => 'payment/PaymentMethod/fields.html.twig',
            ],
            'monolog.use_microseconds' => true,
            'monolog.swift_mailer.handlers' => [

            ],
            'monolog.handlers_to_channels' => [
                'monolog.handler.console' => [
                    'type' => 'exclusive',
                    'elements' => [
                        0 => 'doctrine',
                        1 => 'translation',
                    ],
                ],
                'monolog.handler.php' => [
                    'type' => 'inclusive',
                    'elements' => [
                        0 => 'php',
                    ],
                ],
                'monolog.handler.flash' => [
                    'type' => 'inclusive',
                    'elements' => [
                        0 => 'flash',
                    ],
                ],
                'monolog.handler.ups' => [
                    'type' => 'inclusive',
                    'elements' => [
                        0 => 'ups',
                    ],
                ],
                'monolog.handler.doctrine' => [
                    'type' => 'inclusive',
                    'elements' => [
                        0 => 'doctrine',
                    ],
                ],
                'monolog.handler.email' => [
                    'type' => 'inclusive',
                    'elements' => [
                        0 => 'email',
                    ],
                ],
                'monolog.handler.automation' => [
                    'type' => 'inclusive',
                    'elements' => [
                        0 => 'automation',
                    ],
                ],
                'monolog.handler.production' => [
                    'type' => 'inclusive',
                    'elements' => [
                        0 => 'supplier',
                        1 => 'manufacturing',
                        2 => 'receiving',
                    ],
                ],
                'monolog.handler.sentry' => NULL,
            ],
            'swiftmailer.mailer.default.transport.name' => 'fake_transport',
            'swiftmailer.mailer.default.transport.smtp.encryption' => 'tls',
            'swiftmailer.mailer.default.transport.smtp.port' => 587,
            'swiftmailer.mailer.default.transport.smtp.host' => 'email-smtp.us-west-2.amazonaws.com',
            'swiftmailer.mailer.default.transport.smtp.username' => 'AKIAI5WM7Y2VUODDW23Q',
            'swiftmailer.mailer.default.transport.smtp.password' => '',
            'swiftmailer.mailer.default.transport.smtp.auth_mode' => NULL,
            'swiftmailer.mailer.default.transport.smtp.timeout' => 30,
            'swiftmailer.mailer.default.transport.smtp.source_ip' => NULL,
            'swiftmailer.mailer.default.transport.smtp.local_domain' => NULL,
            'swiftmailer.mailer.default.spool.enabled' => false,
            'swiftmailer.mailer.default.plugin.impersonate' => NULL,
            'swiftmailer.mailer.default.single_address' => NULL,
            'swiftmailer.mailer.default.delivery.enabled' => true,
            'swiftmailer.spool.enabled' => false,
            'swiftmailer.delivery.enabled' => true,
            'swiftmailer.single_address' => NULL,
            'swiftmailer.mailers' => [
                'default' => 'swiftmailer.mailer.default',
            ],
            'swiftmailer.default_mailer' => 'default',
            'craue.form.flow.class' => 'Craue\\FormFlowBundle\\Form\\FormFlow',
            'craue.form.flow.storage.class' => 'Craue\\FormFlowBundle\\Storage\\SessionStorage',
            'craue.form.flow.event_listener.previous_step_invalid.class' => 'Craue\\FormFlowBundle\\EventListener\\PreviousStepInvalidEventListener',
            'craue.form.flow.event_listener.previous_step_invalid.event' => 'flow.previous_step_invalid',
            'craue.form.flow.event_listener.flow_expired.class' => 'Craue\\FormFlowBundle\\EventListener\\FlowExpiredEventListener',
            'craue.form.flow.event_listener.flow_expired.event' => 'flow.flow_expired',
            'craue_twig_extensions.formflow.class' => 'Craue\\FormFlowBundle\\Twig\\Extension\\FormFlowExtension',
            'craue_formflow.util.class' => 'Craue\\FormFlowBundle\\Util\\FormFlowUtil',
            'doctrine_cache.apc.class' => 'Doctrine\\Common\\Cache\\ApcCache',
            'doctrine_cache.apcu.class' => 'Doctrine\\Common\\Cache\\ApcuCache',
            'doctrine_cache.array.class' => 'Doctrine\\Common\\Cache\\ArrayCache',
            'doctrine_cache.chain.class' => 'Doctrine\\Common\\Cache\\ChainCache',
            'doctrine_cache.couchbase.class' => 'Doctrine\\Common\\Cache\\CouchbaseCache',
            'doctrine_cache.couchbase.connection.class' => 'Couchbase',
            'doctrine_cache.couchbase.hostnames' => 'localhost:8091',
            'doctrine_cache.file_system.class' => 'Doctrine\\Common\\Cache\\FilesystemCache',
            'doctrine_cache.php_file.class' => 'Doctrine\\Common\\Cache\\PhpFileCache',
            'doctrine_cache.memcache.class' => 'Doctrine\\Common\\Cache\\MemcacheCache',
            'doctrine_cache.memcache.connection.class' => 'Memcache',
            'doctrine_cache.memcache.host' => 'localhost',
            'doctrine_cache.memcache.port' => 11211,
            'doctrine_cache.memcached.class' => 'Doctrine\\Common\\Cache\\MemcachedCache',
            'doctrine_cache.memcached.connection.class' => 'Memcached',
            'doctrine_cache.memcached.host' => 'localhost',
            'doctrine_cache.memcached.port' => 11211,
            'doctrine_cache.mongodb.class' => 'Doctrine\\Common\\Cache\\MongoDBCache',
            'doctrine_cache.mongodb.collection.class' => 'MongoCollection',
            'doctrine_cache.mongodb.connection.class' => 'MongoClient',
            'doctrine_cache.mongodb.server' => 'localhost:27017',
            'doctrine_cache.predis.client.class' => 'Predis\\Client',
            'doctrine_cache.predis.scheme' => 'tcp',
            'doctrine_cache.predis.host' => 'localhost',
            'doctrine_cache.predis.port' => 6379,
            'doctrine_cache.redis.class' => 'Doctrine\\Common\\Cache\\RedisCache',
            'doctrine_cache.redis.connection.class' => 'Redis',
            'doctrine_cache.redis.host' => 'localhost',
            'doctrine_cache.redis.port' => 6379,
            'doctrine_cache.riak.class' => 'Doctrine\\Common\\Cache\\RiakCache',
            'doctrine_cache.riak.bucket.class' => 'Riak\\Bucket',
            'doctrine_cache.riak.connection.class' => 'Riak\\Connection',
            'doctrine_cache.riak.bucket_property_list.class' => 'Riak\\BucketPropertyList',
            'doctrine_cache.riak.host' => 'localhost',
            'doctrine_cache.riak.port' => 8087,
            'doctrine_cache.sqlite3.class' => 'Doctrine\\Common\\Cache\\SQLite3Cache',
            'doctrine_cache.sqlite3.connection.class' => 'SQLite3',
            'doctrine_cache.void.class' => 'Doctrine\\Common\\Cache\\VoidCache',
            'doctrine_cache.wincache.class' => 'Doctrine\\Common\\Cache\\WinCacheCache',
            'doctrine_cache.xcache.class' => 'Doctrine\\Common\\Cache\\XcacheCache',
            'doctrine_cache.zenddata.class' => 'Doctrine\\Common\\Cache\\ZendDataCache',
            'doctrine_cache.security.acl.cache.class' => 'Doctrine\\Bundle\\DoctrineCacheBundle\\Acl\\Model\\AclCache',
            'doctrine.dbal.logger.chain.class' => 'Doctrine\\DBAL\\Logging\\LoggerChain',
            'doctrine.dbal.logger.profiling.class' => 'Doctrine\\DBAL\\Logging\\DebugStack',
            'doctrine.dbal.logger.class' => 'Symfony\\Bridge\\Doctrine\\Logger\\DbalLogger',
            'doctrine.dbal.configuration.class' => 'Doctrine\\DBAL\\Configuration',
            'doctrine.data_collector.class' => 'Doctrine\\Bundle\\DoctrineBundle\\DataCollector\\DoctrineDataCollector',
            'doctrine.dbal.connection.event_manager.class' => 'Symfony\\Bridge\\Doctrine\\ContainerAwareEventManager',
            'doctrine.dbal.connection_factory.class' => 'Doctrine\\Bundle\\DoctrineBundle\\ConnectionFactory',
            'doctrine.dbal.events.mysql_session_init.class' => 'Doctrine\\DBAL\\Event\\Listeners\\MysqlSessionInit',
            'doctrine.dbal.events.oracle_session_init.class' => 'Doctrine\\DBAL\\Event\\Listeners\\OracleSessionInit',
            'doctrine.class' => 'Doctrine\\Bundle\\DoctrineBundle\\Registry',
            'doctrine.entity_managers' => [
                'default' => 'doctrine.orm.default_entity_manager',
            ],
            'doctrine.default_entity_manager' => 'default',
            'doctrine.dbal.connection_factory.types' => [
                'jms_job_safe_object' => [
                    'class' => 'JMS\\JobQueueBundle\\Entity\\Type\\SafeObjectType',
                    'commented' => NULL,
                ],
                'vector2d' => [
                    'class' => 'Gumstix\\Geometry\\Orm\\Vector2DType',
                    'commented' => NULL,
                ],
            ],
            'doctrine.connections' => [
                'default' => 'doctrine.dbal.default_connection',
            ],
            'doctrine.default_connection' => 'default',
            'doctrine.orm.configuration.class' => 'Doctrine\\ORM\\Configuration',
            'doctrine.orm.entity_manager.class' => 'Doctrine\\ORM\\EntityManager',
            'doctrine.orm.manager_configurator.class' => 'Doctrine\\Bundle\\DoctrineBundle\\ManagerConfigurator',
            'doctrine.orm.cache.array.class' => 'Doctrine\\Common\\Cache\\ArrayCache',
            'doctrine.orm.cache.apc.class' => 'Doctrine\\Common\\Cache\\ApcCache',
            'doctrine.orm.cache.memcache.class' => 'Doctrine\\Common\\Cache\\MemcacheCache',
            'doctrine.orm.cache.memcache_host' => 'localhost',
            'doctrine.orm.cache.memcache_port' => 11211,
            'doctrine.orm.cache.memcache_instance.class' => 'Memcache',
            'doctrine.orm.cache.memcached.class' => 'Doctrine\\Common\\Cache\\MemcachedCache',
            'doctrine.orm.cache.memcached_host' => 'localhost',
            'doctrine.orm.cache.memcached_port' => 11211,
            'doctrine.orm.cache.memcached_instance.class' => 'Memcached',
            'doctrine.orm.cache.redis.class' => 'Doctrine\\Common\\Cache\\RedisCache',
            'doctrine.orm.cache.redis_host' => 'localhost',
            'doctrine.orm.cache.redis_port' => 6379,
            'doctrine.orm.cache.redis_instance.class' => 'Redis',
            'doctrine.orm.cache.xcache.class' => 'Doctrine\\Common\\Cache\\XcacheCache',
            'doctrine.orm.cache.wincache.class' => 'Doctrine\\Common\\Cache\\WinCacheCache',
            'doctrine.orm.cache.zenddata.class' => 'Doctrine\\Common\\Cache\\ZendDataCache',
            'doctrine.orm.metadata.driver_chain.class' => 'Doctrine\\Common\\Persistence\\Mapping\\Driver\\MappingDriverChain',
            'doctrine.orm.metadata.annotation.class' => 'Doctrine\\ORM\\Mapping\\Driver\\AnnotationDriver',
            'doctrine.orm.metadata.xml.class' => 'Doctrine\\ORM\\Mapping\\Driver\\SimplifiedXmlDriver',
            'doctrine.orm.metadata.yml.class' => 'Doctrine\\ORM\\Mapping\\Driver\\SimplifiedYamlDriver',
            'doctrine.orm.metadata.php.class' => 'Doctrine\\ORM\\Mapping\\Driver\\PHPDriver',
            'doctrine.orm.metadata.staticphp.class' => 'Doctrine\\ORM\\Mapping\\Driver\\StaticPHPDriver',
            'doctrine.orm.proxy_cache_warmer.class' => 'Symfony\\Bridge\\Doctrine\\CacheWarmer\\ProxyCacheWarmer',
            'form.type_guesser.doctrine.class' => 'Symfony\\Bridge\\Doctrine\\Form\\DoctrineOrmTypeGuesser',
            'doctrine.orm.validator.unique.class' => 'Symfony\\Bridge\\Doctrine\\Validator\\Constraints\\UniqueEntityValidator',
            'doctrine.orm.validator_initializer.class' => 'Symfony\\Bridge\\Doctrine\\Validator\\DoctrineInitializer',
            'doctrine.orm.security.user.provider.class' => 'Symfony\\Bridge\\Doctrine\\Security\\User\\EntityUserProvider',
            'doctrine.orm.listeners.resolve_target_entity.class' => 'Doctrine\\ORM\\Tools\\ResolveTargetEntityListener',
            'doctrine.orm.listeners.attach_entity_listeners.class' => 'Doctrine\\ORM\\Tools\\AttachEntityListenersListener',
            'doctrine.orm.naming_strategy.default.class' => 'Doctrine\\ORM\\Mapping\\DefaultNamingStrategy',
            'doctrine.orm.naming_strategy.underscore.class' => 'Doctrine\\ORM\\Mapping\\UnderscoreNamingStrategy',
            'doctrine.orm.quote_strategy.default.class' => 'Doctrine\\ORM\\Mapping\\DefaultQuoteStrategy',
            'doctrine.orm.quote_strategy.ansi.class' => 'Doctrine\\ORM\\Mapping\\AnsiQuoteStrategy',
            'doctrine.orm.entity_listener_resolver.class' => 'Doctrine\\Bundle\\DoctrineBundle\\Mapping\\ContainerEntityListenerResolver',
            'doctrine.orm.second_level_cache.default_cache_factory.class' => 'Doctrine\\ORM\\Cache\\DefaultCacheFactory',
            'doctrine.orm.second_level_cache.default_region.class' => 'Doctrine\\ORM\\Cache\\Region\\DefaultRegion',
            'doctrine.orm.second_level_cache.filelock_region.class' => 'Doctrine\\ORM\\Cache\\Region\\FileLockRegion',
            'doctrine.orm.second_level_cache.logger_chain.class' => 'Doctrine\\ORM\\Cache\\Logging\\CacheLoggerChain',
            'doctrine.orm.second_level_cache.logger_statistics.class' => 'Doctrine\\ORM\\Cache\\Logging\\StatisticsCacheLogger',
            'doctrine.orm.second_level_cache.cache_configuration.class' => 'Doctrine\\ORM\\Cache\\CacheConfiguration',
            'doctrine.orm.second_level_cache.regions_configuration.class' => 'Doctrine\\ORM\\Cache\\RegionsConfiguration',
            'doctrine.orm.auto_generate_proxy_classes' => true,
            'doctrine.orm.proxy_namespace' => 'Proxies',
            'doctrine_migrations.namespace' => 'Rialto\\Migrations',
            'doctrine_migrations.table_name' => 'database_migration',
            'doctrine_migrations.name' => 'Database Migrations',
            'doctrine_migrations.custom_template' => NULL,
            'doctrine_migrations.organize_migrations' => false,
            'sensio_framework_extra.view.guesser.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\Templating\\TemplateGuesser',
            'sensio_framework_extra.controller.listener.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\ControllerListener',
            'sensio_framework_extra.routing.loader.annot_dir.class' => 'Symfony\\Component\\Routing\\Loader\\AnnotationDirectoryLoader',
            'sensio_framework_extra.routing.loader.annot_file.class' => 'Symfony\\Component\\Routing\\Loader\\AnnotationFileLoader',
            'sensio_framework_extra.routing.loader.annot_class.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\Routing\\AnnotatedRouteControllerLoader',
            'sensio_framework_extra.converter.listener.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\ParamConverterListener',
            'sensio_framework_extra.converter.manager.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\Request\\ParamConverter\\ParamConverterManager',
            'sensio_framework_extra.converter.doctrine.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\Request\\ParamConverter\\DoctrineParamConverter',
            'sensio_framework_extra.converter.datetime.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\Request\\ParamConverter\\DateTimeParamConverter',
            'sensio_framework_extra.view.listener.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\TemplateListener',
            'easyadmin.config' => [
                'site_name' => 'Rialto',
                'list' => [
                    'max_results' => 30,
                    'actions' => [

                    ],
                ],
                'design' => [
                    'menu' => [
                        0 => [
                            'label' => 'Home',
                            'route' => 'index',
                        ],
                        1 => 'GLAccount',
                        2 => 'Role',
                        3 => 'BinStyle',
                        4 => 'TaxAuthority',
                        5 => 'Company',
                    ],
                    'theme' => 'default',
                    'color_scheme' => 'dark',
                    'brand_color' => '#205081',
                    'form_theme' => [
                        0 => '@EasyAdmin/form/bootstrap_3_horizontal_layout.html.twig',
                    ],
                    'assets' => [
                        'css' => [

                        ],
                        'js' => [

                        ],
                        'favicon' => [
                            'path' => 'favicon.ico',
                            'mime_type' => 'image/x-icon',
                        ],
                    ],
                ],
                'entities' => [
                    'GLAccount' => [
                        'class' => 'Rialto\\Accounting\\Ledger\\Account\\GLAccount',
                        'label' => 'Accounting : GL Account',
                        'disabled_actions' => [
                            0 => 'delete',
                        ],
                        'list' => [
                            'fields' => [
                                0 => 'id',
                                1 => 'name',
                                2 => 'groupName',
                            ],
                            'sort' => [
                                0 => 'id',
                                1 => 'ASC',
                            ],
                        ],
                        'form' => [
                            'fields' => [
                                0 => 'id',
                                1 => 'name',
                                2 => 'accountGroup',
                            ],
                        ],
                        'name' => 'GLAccount',
                    ],
                    'Role' => [
                        'class' => 'Rialto\\Security\\Role\\Role',
                        'label' => 'Security : Role',
                        'disabled_actions' => [
                            0 => 'new',
                            1 => 'delete',
                        ],
                        'list' => [
                            'fields' => [
                                0 => 'role',
                                1 => 'group',
                                2 => 'label',
                            ],
                        ],
                        'form' => [
                            'fields' => [
                                0 => 'group',
                                1 => 'label',
                            ],
                        ],
                        'name' => 'Role',
                    ],
                    'BinStyle' => [
                        'class' => 'Rialto\\Stock\\Bin\\BinStyle',
                        'label' => 'Stock : Bin style',
                        'disabled_actions' => [
                            0 => 'delete',
                        ],
                        'list' => [
                            'fields' => [
                                0 => 'id',
                                1 => 'name',
                                2 => 'numLabels',
                            ],
                        ],
                        'new' => [
                            'fields' => [
                                0 => 'id',
                                1 => 'name',
                                2 => 'numLabels',
                            ],
                        ],
                        'name' => 'BinStyle',
                    ],
                    'TaxAuthority' => [
                        'class' => 'Rialto\\Tax\\Authority\\TaxAuthority',
                        'label' => 'Tax : Tax Authority',
                        'disabled_actions' => [
                            0 => 'new',
                            1 => 'delete',
                        ],
                        'name' => 'TaxAuthority',
                    ],
                    'Company' => [
                        'class' => 'Rialto\\Company\\Company',
                        'label' => 'Company',
                        'disabled_actions' => [
                            0 => 'new',
                            1 => 'delete',
                        ],
                        'list' => [
                            'fields' => [
                                0 => 'id',
                                1 => 'name',
                                2 => 'regOffice1',
                                3 => 'regOffice2',
                                4 => 'regOffice3',
                            ],
                        ],
                        'form' => [
                            'fields' => [
                                0 => 'name',
                                1 => 'regOffice1',
                                2 => 'regOffice2',
                                3 => 'regOffice3',
                            ],
                        ],
                        'name' => 'Company',
                    ],
                ],
                'formats' => [
                    'date' => 'Y-m-d',
                    'time' => 'H:i:s',
                    'datetime' => 'F j, Y H:i',
                    'dateinterval' => '%y Year(s) %m Month(s) %d Day(s)',
                ],
                'disabled_actions' => [

                ],
                'translation_domain' => 'messages',
                'search' => [

                ],
                'edit' => [
                    'actions' => [

                    ],
                ],
                'new' => [
                    'actions' => [

                    ],
                ],
                'show' => [
                    'actions' => [

                    ],
                    'max_results' => 10,
                ],
            ],
            'jms_job_queue.entity.many_to_any_listener.class' => 'JMS\\JobQueueBundle\\Entity\\Listener\\ManyToAnyListener',
            'jms_job_queue.twig.extension.class' => 'JMS\\JobQueueBundle\\Twig\\JobQueueExtension',
            'jms_job_queue.retry_scheduler.class' => 'JMS\\JobQueueBundle\\Retry\\ExponentialRetryScheduler',
            'jms_job_queue.job_manager.class' => 'JMS\\JobQueueBundle\\Entity\\Repository\\JobManager',
            'jms_job_queue.statistics' => true,
            'jms_job_queue.entity.statistics_listener.class' => 'JMS\\JobQueueBundle\\Entity\\Listener\\StatisticsListener',
            'jms_job_queue.queue_options_defaults' => [
                'max_concurrent_jobs' => 3,
            ],
            'jms_job_queue.queue_options' => [

            ],
            'jms_serializer.metadata.file_locator.class' => 'Metadata\\Driver\\FileLocator',
            'jms_serializer.metadata.annotation_driver.class' => 'JMS\\Serializer\\Metadata\\Driver\\AnnotationDriver',
            'jms_serializer.metadata.chain_driver.class' => 'Metadata\\Driver\\DriverChain',
            'jms_serializer.metadata.yaml_driver.class' => 'JMS\\Serializer\\Metadata\\Driver\\YamlDriver',
            'jms_serializer.metadata.xml_driver.class' => 'JMS\\Serializer\\Metadata\\Driver\\XmlDriver',
            'jms_serializer.metadata.php_driver.class' => 'JMS\\Serializer\\Metadata\\Driver\\PhpDriver',
            'jms_serializer.metadata.doctrine_type_driver.class' => 'JMS\\Serializer\\Metadata\\Driver\\DoctrineTypeDriver',
            'jms_serializer.metadata.doctrine_phpcr_type_driver.class' => 'JMS\\Serializer\\Metadata\\Driver\\DoctrinePHPCRTypeDriver',
            'jms_serializer.metadata.lazy_loading_driver.class' => 'Metadata\\Driver\\LazyLoadingDriver',
            'jms_serializer.metadata.metadata_factory.class' => 'Metadata\\MetadataFactory',
            'jms_serializer.metadata.cache.file_cache.class' => 'Metadata\\Cache\\FileCache',
            'jms_serializer.event_dispatcher.class' => 'JMS\\Serializer\\EventDispatcher\\LazyEventDispatcher',
            'jms_serializer.camel_case_naming_strategy.class' => 'JMS\\Serializer\\Naming\\CamelCaseNamingStrategy',
            'jms_serializer.identical_property_naming_strategy.class' => 'JMS\\Serializer\\Naming\\IdenticalPropertyNamingStrategy',
            'jms_serializer.serialized_name_annotation_strategy.class' => 'JMS\\Serializer\\Naming\\SerializedNameAnnotationStrategy',
            'jms_serializer.cache_naming_strategy.class' => 'JMS\\Serializer\\Naming\\CacheNamingStrategy',
            'jms_serializer.doctrine_object_constructor.class' => 'JMS\\Serializer\\Construction\\DoctrineObjectConstructor',
            'jms_serializer.unserialize_object_constructor.class' => 'JMS\\Serializer\\Construction\\UnserializeObjectConstructor',
            'jms_serializer.version_exclusion_strategy.class' => 'JMS\\Serializer\\Exclusion\\VersionExclusionStrategy',
            'jms_serializer.serializer.class' => 'JMS\\Serializer\\Serializer',
            'jms_serializer.twig_extension.class' => 'JMS\\Serializer\\Twig\\SerializerExtension',
            'jms_serializer.twig_runtime_extension.class' => 'JMS\\Serializer\\Twig\\SerializerRuntimeExtension',
            'jms_serializer.twig_runtime_extension_helper.class' => 'JMS\\Serializer\\Twig\\SerializerRuntimeHelper',
            'jms_serializer.templating.helper.class' => 'JMS\\SerializerBundle\\Templating\\SerializerHelper',
            'jms_serializer.json_serialization_visitor.class' => 'JMS\\Serializer\\JsonSerializationVisitor',
            'jms_serializer.json_serialization_visitor.options' => 0,
            'jms_serializer.json_deserialization_visitor.class' => 'JMS\\Serializer\\JsonDeserializationVisitor',
            'jms_serializer.xml_serialization_visitor.class' => 'JMS\\Serializer\\XmlSerializationVisitor',
            'jms_serializer.xml_deserialization_visitor.class' => 'JMS\\Serializer\\XmlDeserializationVisitor',
            'jms_serializer.xml_deserialization_visitor.doctype_whitelist' => [

            ],
            'jms_serializer.xml_serialization_visitor.format_output' => true,
            'jms_serializer.yaml_serialization_visitor.class' => 'JMS\\Serializer\\YamlSerializationVisitor',
            'jms_serializer.handler_registry.class' => 'JMS\\Serializer\\Handler\\LazyHandlerRegistry',
            'jms_serializer.datetime_handler.class' => 'JMS\\Serializer\\Handler\\DateHandler',
            'jms_serializer.array_collection_handler.class' => 'JMS\\Serializer\\Handler\\ArrayCollectionHandler',
            'jms_serializer.php_collection_handler.class' => 'JMS\\Serializer\\Handler\\PhpCollectionHandler',
            'jms_serializer.form_error_handler.class' => 'JMS\\Serializer\\Handler\\FormErrorHandler',
            'jms_serializer.constraint_violation_handler.class' => 'JMS\\Serializer\\Handler\\ConstraintViolationHandler',
            'jms_serializer.doctrine_proxy_subscriber.class' => 'JMS\\Serializer\\EventDispatcher\\Subscriber\\DoctrineProxySubscriber',
            'jms_serializer.stopwatch_subscriber.class' => 'JMS\\SerializerBundle\\Serializer\\StopwatchEventSubscriber',
            'jms_serializer.configured_context_factory.class' => 'JMS\\SerializerBundle\\ContextFactory\\ConfiguredContextFactory',
            'jms_serializer.expression_evaluator.class' => 'JMS\\Serializer\\Expression\\ExpressionEvaluator',
            'jms_serializer.expression_language.class' => 'Symfony\\Component\\ExpressionLanguage\\ExpressionLanguage',
            'jms_serializer.expression_language.function_provider.class' => 'JMS\\SerializerBundle\\ExpressionLanguage\\BasicSerializerFunctionsProvider',
            'jms_serializer.accessor_strategy.default.class' => 'JMS\\Serializer\\Accessor\\DefaultAccessorStrategy',
            'jms_serializer.accessor_strategy.expression.class' => 'JMS\\Serializer\\Accessor\\ExpressionAccessorStrategy',
            'jms_serializer.cache.cache_warmer.class' => 'JMS\\SerializerBundle\\Cache\\CacheWarmer',
            'fos_rest.format_listener.rules' => NULL,
            'fos_js_routing.extractor.class' => 'FOS\\JsRoutingBundle\\Extractor\\ExposedRoutesExtractor',
            'fos_js_routing.controller.class' => 'FOS\\JsRoutingBundle\\Controller\\Controller',
            'fos_js_routing.normalizer.route_collection.class' => 'FOS\\JsRoutingBundle\\Serializer\\Normalizer\\RouteCollectionNormalizer',
            'fos_js_routing.normalizer.routes_response.class' => 'FOS\\JsRoutingBundle\\Serializer\\Normalizer\\RoutesResponseNormalizer',
            'fos_js_routing.denormalizer.route_collection.class' => 'FOS\\JsRoutingBundle\\Serializer\\Denormalizer\\RouteCollectionDenormalizer',
            'fos_js_routing.request_context_base_url' => NULL,
            'fos_js_routing.cache_control' => [
                'enabled' => false,
            ],
            'nelmio_security.clickjacking.paths' => [
                '^/.*' => [
                    'header' => 'SAMEORIGIN',
                ],
            ],
            'nelmio_security.clickjacking.content_types' => [

            ],
            'nelmio_security.external_redirects.whitelist' => '(?:.*\\.mystix\\.com|.*\\.dev\\-storefront\\.pcbng\\.com|mystix\\.com|dev\\-storefront\\.pcbng\\.com)',
            'nelmio_security.external_redirects.override' => NULL,
            'nelmio_security.external_redirects.forward_as' => NULL,
            'nelmio_security.external_redirects.abort' => true,
            'nelmio_cors.map' => [
                '^/api/' => [
                    'origin_regex' => true,
                    'allow_origin' => [
                        0 => '^http://(.+.)?mystix.com',
                    ],
                    'allow_headers' => [
                        0 => 'origin',
                        1 => 'content-type',
                    ],
                    'allow_methods' => [
                        0 => 'POST',
                        1 => 'PUT',
                        2 => 'GET',
                        3 => 'DELETE',
                        4 => 'OPTIONS',
                        5 => 'PATCH',
                    ],
                    'max_age' => 3600,
                ],
            ],
            'nelmio_cors.defaults' => [
                'allow_origin' => [

                ],
                'allow_credentials' => true,
                'allow_headers' => [

                ],
                'expose_headers' => [

                ],
                'allow_methods' => [

                ],
                'max_age' => 0,
                'hosts' => [

                ],
                'origin_regex' => false,
                'forced_allow_origin_value' => NULL,
            ],
            'nelmio_cors.cors_listener.class' => 'Nelmio\\CorsBundle\\EventListener\\CorsListener',
            'nelmio_cors.options_resolver.class' => 'Nelmio\\CorsBundle\\Options\\Resolver',
            'nelmio_cors.options_provider.config.class' => 'Nelmio\\CorsBundle\\Options\\ConfigProvider',
            'gumstix_sso.tokens.consumer_key' => '',
            'gumstix_sso.tokens.consumer_secret' => '',
            'gumstix_sso.service.scope' => 'gumstix',
            'gumstix_sso.cookie.name' => 'gumstix-sso',
            'web_profiler.debug_toolbar.position' => 'bottom',
            'web_profiler.debug_toolbar.intercept_redirects' => false,
            'web_profiler.debug_toolbar.mode' => 2,
            'console.command.ids' => [
                'console.command.rialto_accounting_paymenttransaction_cli_recalculatesettled' => 'console.command.rialto_accounting_paymenttransaction_cli_recalculatesettled',
                'console.command.rialto_allocation_cli_deleteinvalidallocationscommand' => 'console.command.rialto_allocation_cli_deleteinvalidallocationscommand',
                'console.command.rialto_allocation_cli_deletestockbinallocationscommand' => 'console.command.rialto_allocation_cli_deletestockbinallocationscommand',
                'console.command.rialto_logging_cli_recreatemongologscommand' => 'console.command.rialto_logging_cli_recreatemongologscommand',
                'console.command.rialto_magento2_stock_cli_syncstocklevelscommand' => 'console.command.rialto_magento2_stock_cli_syncstocklevelscommand',
                'console.command.rialto_magento2_order_cli_syncorderscommand' => 'console.command.rialto_magento2_order_cli_syncorderscommand',
                'console.command.rialto_manufacturing_customization_cli_validatesubstitutionscommand' => 'console.command.rialto_manufacturing_customization_cli_validatesubstitutionscommand',
                'console.command.rialto_manufacturing_kit_reminder_sendemailcommand' => 'console.command.rialto_manufacturing_kit_reminder_sendemailcommand',
                'console.command.rialto_manufacturing_workorder_cli_autobuildcommand' => 'console.command.rialto_manufacturing_workorder_cli_autobuildcommand',
                'console.command.rialto_manufacturing_task_cli_taskscommand' => 'console.command.rialto_manufacturing_task_cli_taskscommand',
                'console.command.rialto_manufacturing_task_cli_refreshproductiontaskscommand' => 'console.command.rialto_manufacturing_task_cli_refreshproductiontaskscommand',
                'console.command.rialto_manufacturing_task_cli_productiontaskremindercommand' => 'console.command.rialto_manufacturing_task_cli_productiontaskremindercommand',
                'console.command.rialto_manufacturing_task_cli_jobscommand' => 'console.command.rialto_manufacturing_task_cli_jobscommand',
                'console.command.rialto_payment_sweep_cli_sweepcardtransactionscommand' => 'console.command.rialto_payment_sweep_cli_sweepcardtransactionscommand',
                'console.command.rialto_printing_job_cli_flushprintqueue' => 'console.command.rialto_printing_job_cli_flushprintqueue',
                'console.command.rialto_printing_job_cli_deletecompletedprintjobs' => 'console.command.rialto_printing_job_cli_deletecompletedprintjobs',
                'console.command.rialto_printing_printer_cli_devprintserver' => 'console.command.rialto_printing_printer_cli_devprintserver',
                'console.command.rialto_purchasing_catalog_cli_purchasingdatasynchronizercommand' => 'Rialto\\Purchasing\\Catalog\\Cli\\PurchasingDataSynchronizerCommand',
                'console.command.rialto_purchasing_catalog_cli_purchasingdatastocklevelrefreshcommand' => 'Rialto\\Purchasing\\Catalog\\Cli\\PurchasingDataStockLevelRefreshCommand',
                'console.command.rialto_purchasing_catalog_cli_refreshgeppettopurchasingdataconsolecommand' => 'Rialto\\Purchasing\\Catalog\\Cli\\RefreshGeppettoPurchasingDataConsoleCommand',
                'console.command.rialto_purchasing_invoice_reader_email_cli_autoimportinvoices' => 'console.command.rialto_purchasing_invoice_reader_email_cli_autoimportinvoices',
                'console.command.rialto_purchasing_invoice_cli_finduninvoicedorders' => 'console.command.rialto_purchasing_invoice_cli_finduninvoicedorders',
                'console.command.rialto_purchasing_recurring_cli_autoinvoicecommand' => 'console.command.rialto_purchasing_recurring_cli_autoinvoicecommand',
                'console.command.rialto_purchasing_manufacturer_cli_bulkpushmodulemanufacturersconsolecommand' => 'Rialto\\Purchasing\\Manufacturer\\Cli\\BulkPushModuleManufacturersConsoleCommand',
                'console.command.rialto_purchasing_order_cli_autoordercommand' => 'console.command.rialto_purchasing_order_cli_autoordercommand',
                'console.command.rialto_purchasing_receiving_notify_testxmppcommand' => 'console.command.rialto_purchasing_receiving_notify_testxmppcommand',
                'console.command.rialto_sales_order_dates_inittargetdatecommand' => 'console.command.rialto_sales_order_dates_inittargetdatecommand',
                'console.command.rialto_security_user_cli_createusercommand' => 'console.command.rialto_security_user_cli_createusercommand',
                'console.command.rialto_security_user_cli_promoteusercommand' => 'console.command.rialto_security_user_cli_promoteusercommand',
                'console.command.rialto_security_user_cli_adduuidcommand' => 'console.command.rialto_security_user_cli_adduuidcommand',
                'console.command.rialto_shopify_webhook_cli_webhookcustomcommand' => 'console.command.rialto_shopify_webhook_cli_webhookcustomcommand',
                'console.command.rialto_stock_item_cli_bulksetdefaultworkordercommand' => 'console.command.rialto_stock_item_cli_bulksetdefaultworkordercommand',
                'console.command.rialto_stock_item_cli_stocklevelrefreshcommand' => 'Rialto\\Stock\\Item\\Cli\\StockLevelRefreshCommand',
                'console.command.rialto_stock_level_cli_stocklevelsynccommand' => 'console.command.rialto_stock_level_cli_stocklevelsynccommand',
                'console.command.rialto_stock_returns_cli_generatemissingadjustmentglrecordscommand' => 'console.command.rialto_stock_returns_cli_generatemissingadjustmentglrecordscommand',
                'console.command.rialto_tax_regime_cli_loadtaxregimescommand' => 'console.command.rialto_tax_regime_cli_loadtaxregimescommand',
                'console.command.rialto_ups_trackingrecord_cli_polltrackingnumberscommand' => 'Rialto\\Ups\\TrackingRecord\\Cli\\PollTrackingNumbersCommand',
                'console.command.rialto_ups_trackingrecord_cli_updatepotrackingrecordscommand' => 'Rialto\\Ups\\TrackingRecord\\Cli\\UpdatePOTrackingRecordsCommand',
                'console.command.rialto_ups_trackingrecord_cli_updatesalestrackingrecordscommand' => 'Rialto\\Ups\\TrackingRecord\\Cli\\UpdateSalesTrackingRecordsCommand',
                'console.command.rialto_port_commandbus_handlecommandconsolecommand' => 'console.command.rialto_port_commandbus_handlecommandconsolecommand',
                'console.command.symfony_bundle_frameworkbundle_command_aboutcommand' => 'console.command.about',
                'console.command.symfony_bundle_frameworkbundle_command_assetsinstallcommand' => 'console.command.assets_install',
                'console.command.symfony_bundle_frameworkbundle_command_cacheclearcommand' => 'console.command.cache_clear',
                'console.command.symfony_bundle_frameworkbundle_command_cachepoolclearcommand' => 'console.command.cache_pool_clear',
                'console.command.symfony_bundle_frameworkbundle_command_cachepoolprunecommand' => 'console.command.cache_pool_prune',
                'console.command.symfony_bundle_frameworkbundle_command_cachewarmupcommand' => 'console.command.cache_warmup',
                'console.command.symfony_bundle_frameworkbundle_command_configdebugcommand' => 'console.command.config_debug',
                'console.command.symfony_bundle_frameworkbundle_command_configdumpreferencecommand' => 'console.command.config_dump_reference',
                'console.command.symfony_bundle_frameworkbundle_command_containerdebugcommand' => 'console.command.container_debug',
                'console.command.symfony_bundle_frameworkbundle_command_debugautowiringcommand' => 'console.command.debug_autowiring',
                'console.command.symfony_bundle_frameworkbundle_command_eventdispatcherdebugcommand' => 'console.command.event_dispatcher_debug',
                'console.command.symfony_bundle_frameworkbundle_command_routerdebugcommand' => 'console.command.router_debug',
                'console.command.symfony_bundle_frameworkbundle_command_routermatchcommand' => 'console.command.router_match',
                'console.command.symfony_bundle_frameworkbundle_command_translationdebugcommand' => 'console.command.translation_debug',
                'console.command.symfony_bundle_frameworkbundle_command_translationupdatecommand' => 'console.command.translation_update',
                'console.command.symfony_bundle_frameworkbundle_command_xlifflintcommand' => 'console.command.xliff_lint',
                'console.command.symfony_bundle_frameworkbundle_command_yamllintcommand' => 'console.command.yaml_lint',
                'console.command.symfony_component_form_command_debugcommand' => 'console.command.form_debug',
                'console.command.symfony_bundle_securitybundle_command_userpasswordencodercommand' => 'security.command.user_password_encoder',
                'console.command.symfony_bridge_twig_command_debugcommand' => 'twig.command.debug',
                'console.command.symfony_bundle_twigbundle_command_lintcommand' => 'twig.command.lint',
                'console.command.symfony_bundle_swiftmailerbundle_command_debugcommand' => 'swiftmailer.command.debug',
                'console.command.symfony_bundle_swiftmailerbundle_command_newemailcommand' => 'swiftmailer.command.new_email',
                'console.command.symfony_bundle_swiftmailerbundle_command_sendemailcommand' => 'swiftmailer.command.send_email',
                'console.command.doctrine_bundle_doctrinecachebundle_command_containscommand' => 'console.command.doctrine_bundle_doctrinecachebundle_command_containscommand',
                'console.command.doctrine_bundle_doctrinecachebundle_command_deletecommand' => 'console.command.doctrine_bundle_doctrinecachebundle_command_deletecommand',
                'console.command.doctrine_bundle_doctrinecachebundle_command_flushcommand' => 'console.command.doctrine_bundle_doctrinecachebundle_command_flushcommand',
                'console.command.doctrine_bundle_doctrinecachebundle_command_statscommand' => 'console.command.doctrine_bundle_doctrinecachebundle_command_statscommand',
                'console.command.doctrine_bundle_doctrinebundle_command_createdatabasedoctrinecommand' => 'doctrine.database_create_command',
                'console.command.doctrine_bundle_doctrinebundle_command_dropdatabasedoctrinecommand' => 'doctrine.database_drop_command',
                'console.command.doctrine_bundle_doctrinebundle_command_generateentitiesdoctrinecommand' => 'doctrine.generate_entities_command',
                'console.command.doctrine_bundle_doctrinebundle_command_proxy_runsqldoctrinecommand' => 'doctrine.query_sql_command',
                'console.command.doctrine_bundle_doctrinebundle_command_proxy_clearmetadatacachedoctrinecommand' => 'doctrine.cache_clear_metadata_command',
                'console.command.doctrine_bundle_doctrinebundle_command_proxy_clearquerycachedoctrinecommand' => 'doctrine.cache_clear_query_cache_command',
                'console.command.doctrine_bundle_doctrinebundle_command_proxy_clearresultcachedoctrinecommand' => 'doctrine.cache_clear_result_command',
                'console.command.doctrine_bundle_doctrinebundle_command_proxy_collectionregiondoctrinecommand' => 'doctrine.cache_collection_region_command',
                'console.command.doctrine_bundle_doctrinebundle_command_proxy_convertmappingdoctrinecommand' => 'doctrine.mapping_convert_command',
                'console.command.doctrine_bundle_doctrinebundle_command_proxy_createschemadoctrinecommand' => 'doctrine.schema_create_command',
                'console.command.doctrine_bundle_doctrinebundle_command_proxy_dropschemadoctrinecommand' => 'doctrine.schema_drop_command',
                'console.command.doctrine_bundle_doctrinebundle_command_proxy_ensureproductionsettingsdoctrinecommand' => 'doctrine.ensure_production_settings_command',
                'console.command.doctrine_bundle_doctrinebundle_command_proxy_entityregioncachedoctrinecommand' => 'doctrine.clear_entity_region_command',
                'console.command.doctrine_bundle_doctrinebundle_command_proxy_importdoctrinecommand' => 'doctrine.database_import_command',
                'console.command.doctrine_bundle_doctrinebundle_command_proxy_infodoctrinecommand' => 'doctrine.mapping_info_command',
                'console.command.doctrine_bundle_doctrinebundle_command_proxy_queryregioncachedoctrinecommand' => 'doctrine.clear_query_region_command',
                'console.command.doctrine_bundle_doctrinebundle_command_proxy_rundqldoctrinecommand' => 'doctrine.query_dql_command',
                'console.command.doctrine_bundle_doctrinebundle_command_proxy_updateschemadoctrinecommand' => 'doctrine.schema_update_command',
                'console.command.doctrine_bundle_doctrinebundle_command_proxy_validateschemacommand' => 'doctrine.schema_validate_command',
                'console.command.doctrine_bundle_doctrinebundle_command_importmappingdoctrinecommand' => 'doctrine.mapping_import_command',
                'console.command.doctrine_bundle_migrationsbundle_command_migrationsdiffdoctrinecommand' => 'console.command.doctrine_bundle_migrationsbundle_command_migrationsdiffdoctrinecommand',
                'console.command.doctrine_bundle_migrationsbundle_command_migrationsexecutedoctrinecommand' => 'console.command.doctrine_bundle_migrationsbundle_command_migrationsexecutedoctrinecommand',
                'console.command.doctrine_bundle_migrationsbundle_command_migrationsgeneratedoctrinecommand' => 'console.command.doctrine_bundle_migrationsbundle_command_migrationsgeneratedoctrinecommand',
                'console.command.doctrine_bundle_migrationsbundle_command_migrationslatestdoctrinecommand' => 'console.command.doctrine_bundle_migrationsbundle_command_migrationslatestdoctrinecommand',
                'console.command.doctrine_bundle_migrationsbundle_command_migrationsmigratedoctrinecommand' => 'console.command.doctrine_bundle_migrationsbundle_command_migrationsmigratedoctrinecommand',
                'console.command.doctrine_bundle_migrationsbundle_command_migrationsstatusdoctrinecommand' => 'console.command.doctrine_bundle_migrationsbundle_command_migrationsstatusdoctrinecommand',
                'console.command.doctrine_bundle_migrationsbundle_command_migrationsversiondoctrinecommand' => 'console.command.doctrine_bundle_migrationsbundle_command_migrationsversiondoctrinecommand',
                'console.command.jms_jobqueuebundle_command_cleanupcommand' => 'jms_job_queue.command.clean_up',
                'console.command.jms_jobqueuebundle_command_markjobincompletecommand' => 'jms_job_queue.command.mark_job_incomplete',
                'console.command.jms_jobqueuebundle_command_runcommand' => 'jms_job_queue.command.run',
                'console.command.jms_jobqueuebundle_command_schedulecommand' => 'jms_job_queue.command.schedule',
                'console.command.fos_jsroutingbundle_command_dumpcommand' => 'fos_js_routing.dump_command',
                'console.command.fos_jsroutingbundle_command_routerdebugexposedcommand' => 'fos_js_routing.router_debug_exposed_command',
                'console.command.league_tactician_bundle_command_debugcommand' => 'console.command.league_tactician_bundle_command_debugcommand',
                'console.command.gumstix_ssobundle_command_setupcommand' => 'console.command.gumstix_ssobundle_command_setupcommand',
            ],
            'console.lazy_command.ids' => [
                'Rialto\\Purchasing\\Manufacturer\\Cli\\BulkPushModuleManufacturersConsoleCommand' => true,
                'console.command.about' => true,
                'console.command.assets_install' => true,
                'console.command.cache_clear' => true,
                'console.command.cache_pool_clear' => true,
                'console.command.cache_pool_prune' => true,
                'console.command.cache_warmup' => true,
                'console.command.config_debug' => true,
                'console.command.config_dump_reference' => true,
                'console.command.container_debug' => true,
                'console.command.debug_autowiring' => true,
                'console.command.event_dispatcher_debug' => true,
                'console.command.router_debug' => true,
                'console.command.router_match' => true,
                'console.command.translation_debug' => true,
                'console.command.translation_update' => true,
                'console.command.xliff_lint' => true,
                'console.command.yaml_lint' => true,
                'console.command.form_debug' => true,
                'security.command.user_password_encoder' => true,
                'twig.command.debug' => true,
                'twig.command.lint' => true,
                'swiftmailer.command.debug' => true,
                'swiftmailer.command.new_email' => true,
                'swiftmailer.command.send_email' => true,
                'doctrine.database_create_command' => true,
                'doctrine.database_drop_command' => true,
                'doctrine.generate_entities_command' => true,
                'doctrine.query_sql_command' => true,
                'doctrine.cache_clear_metadata_command' => true,
                'doctrine.cache_clear_query_cache_command' => true,
                'doctrine.cache_clear_result_command' => true,
                'doctrine.cache_collection_region_command' => true,
                'doctrine.mapping_convert_command' => true,
                'doctrine.schema_create_command' => true,
                'doctrine.schema_drop_command' => true,
                'doctrine.ensure_production_settings_command' => true,
                'doctrine.clear_entity_region_command' => true,
                'doctrine.database_import_command' => true,
                'doctrine.mapping_info_command' => true,
                'doctrine.clear_query_region_command' => true,
                'doctrine.query_dql_command' => true,
                'doctrine.schema_update_command' => true,
                'doctrine.schema_validate_command' => true,
                'doctrine.mapping_import_command' => true,
                'jms_job_queue.command.clean_up' => true,
                'jms_job_queue.command.mark_job_incomplete' => true,
                'jms_job_queue.command.run' => true,
                'jms_job_queue.command.schedule' => true,
                'fos_js_routing.dump_command' => true,
                'fos_js_routing.router_debug_exposed_command' => true,
            ],
        ];
    }
}
