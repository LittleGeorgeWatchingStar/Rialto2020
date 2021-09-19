<?php

namespace Rialto\PcbNg\Service;


use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Rialto\PcbNg\Api\Bundle;
use Rialto\PcbNg\Api\OrderStatus;
use Rialto\PcbNg\Api\Board;
use Rialto\PcbNg\Api\NewBomCartResponse;
use Rialto\PcbNg\Api\Bom;
use Rialto\PcbNg\Api\NewPnpParsingResponse;
use Rialto\PcbNg\Api\NewRfqsResponse;
use Rialto\PcbNg\Api\UserBoard;
use Rialto\PcbNg\Api\PcbNgPart;
use Rialto\PcbNg\Api\PcbPriceQuote;
use Rialto\PcbNg\Api\PostBundleForUnzippingResponse;
use Rialto\PcbNg\Api\PostOrderResponse;
use Rialto\PcbNg\Api\PostPnpResponse;
use Rialto\PcbNg\Api\DfmReports;
use Rialto\PcbNg\Api\BomQuote;
use Rialto\PcbNg\Api\PnpData;
use Rialto\PcbNg\Api\PnpReview;
use Rialto\PcbNg\Api\Quotes;
use Rialto\PcbNg\Api\UnzippedBundle;
use Rialto\PcbNg\Exception\PcbNgClientException;
use Rialto\Purchasing\Supplier\Orm\SupplierRepository;
use Rialto\Purchasing\Supplier\Supplier;

class PcbNgClient
{
    const SUPPLIER_NAME = 'PCB:NG';

    const APP_VER = '2020-04-01-02.05';

    const TIMEOUT = 120; // Seconds.

    const POLL = 1; // Seconds.

    /** @var SupplierRepository */
    private $supplierRepo;

    /** @var string */
    private $storefrontBaseUrl;

    /** @var Client */
    private $pcbNgHttpClient;

    /** @var GerbersConverter */
    private $gerbersConverter;

    /** @var string */
    private $user;

    /** @var string */
    private $password;

    public function __construct(EntityManagerInterface $em,
                                string $storefrontBaseUrl,
                                Client $pcbNgHttpClient,
                                GerbersConverter $gerbersConverter,
                                string $user,
                                string $password)
    {
        $this->supplierRepo = $em->getRepository(Supplier::class);
        $this->storefrontBaseUrl = $storefrontBaseUrl;
        $this->pcbNgHttpClient = $pcbNgHttpClient;
        $this->gerbersConverter = $gerbersConverter;
        $this->user = $user;
        $this->password = $password;
    }

    public function getPcbNgSupplier(): ?Supplier
    {
        /** @var Supplier|null $supplier */
        $supplier = $this->supplierRepo->findOneBy([
            'name' => self::SUPPLIER_NAME,
        ]);

        return $supplier;
    }

    /**
     * @param string $id UserBoard->getId()
     */
    public function getStorefrontBoardUrl(string $id): string
    {
        return "{$this->storefrontBaseUrl}#/boards/$id/pcb";
    }

    /**
     * @throws PcbNgClientException
     */
    private function handleResponse(ResponseInterface $response,
                                    string $errorMessage): void
    {
        if ($response->getStatusCode() !== 200) {
            throw new PcbNgClientException($errorMessage);
        }

        $body = json_decode($response->getBody(), true);

        if (isset($body['error'])) {
            throw new PcbNgClientException("$errorMessage: {$body['error']}");
        }
    }

    /**
     * @return string Auth token.
     * @throws PcbNgClientException
     */
    public function getAuth(): string
    {
        $payload = [
            'email' => $this->user,
            'password' => $this->password,
            'app_version' => self::APP_VER,
        ];

        $response =  $this->pcbNgHttpClient->request(
            'POST',
            'auth/tokens',
            ['json' => $payload]);

        $this->handleResponse($response, 'Authentication failed');

        $body = json_decode($response->getBody(), true);

        return $body['token'];
    }

    /**
     * @param string $auth Auth token.
     * @return array
     */
    private function getHeaders(string $auth): array
    {
        return [
            'Authorization' => "Token $auth",
        ];
    }

    /**
     * Upload a file using file data string.
     *
     * @return string URI of uploaded file.
     * @throws PcbNgClientException
     */
    private function uploadFileData(string $auth,
                                    string $fileData,
                                    string $fileType): string {
        $response = $this->pcbNgHttpClient->request(
            'GET',
            's3/s3auth?contentType=' . str_replace('/', '%2F', $fileType),
            ['headers' => $this->getHeaders($auth),]
        );

        $this->handleResponse($response, 'Could not get S3 upload uri');

        $body = json_decode($response->getBody(), true);
        $s3PutUri = $body['put_url'];
        $s3GetUri = $body['get_url'];

        $putResponse = $this->pcbNgHttpClient->request(
            'PUT',
            $s3PutUri,
            [
                'headers' => [
                    'Content-Type' => $fileType,
                ],
                'body' => $fileData,
            ]
        );

        $this->handleResponse($putResponse, 'File upload failed');

        return $s3GetUri;
    }

    /**
     * Upload a file using file path.
     *
     * @return string URI of uploaded file.
     * @throws PcbNgClientException
     */
    private function uploadFile(string $auth,
                                string $fileName,
                                string $fileType): string
    {
        return $this->uploadFileData($auth, file_get_contents($fileName), $fileType);
    }

    /**
     * @param string $zipBundle URI of uploaded file.
     * @throws PcbNgClientException
     */
    private function postBundleForUnzipping(string $auth,
                                            string $zipBundle,
                                            string $fileName): PostBundleForUnzippingResponse
    {
        $payload = [
            'filename' => $fileName,
            'zip_file_url' => $zipBundle,
        ];

        $response = $this->pcbNgHttpClient->request(
            'POST',
            'gerber/zip_bundles',
            [
                'headers' => $this->getHeaders($auth),
                'json' => $payload,
            ]);

        $this->handleResponse($response, 'New zip bundle creation failure');
        return PostBundleForUnzippingResponse::fromResponse($response);
    }

    private function waitFor(string $auth, string $uri): ResponseInterface
    {
        $getResponse = function () use ($auth, $uri): ResponseInterface {
            return $this->pcbNgHttpClient->request(
                'GET',
                $uri,
                ['headers' => $this->getHeaders($auth)]);
        };

        $response = $getResponse();
        $startTime = time();

        while ($response->getStatusCode() === 404 &&
            time() - $startTime <= self::TIMEOUT) {
            sleep(self::POLL);
            $response = $getResponse();
        }

        return $response;
    }

    /**
     * @throws PcbNgClientException
     */
    private function waitForUnzippedBundle(string $auth,
                                           string $id): UnzippedBundle
    {
        $response = $this->waitFor($auth, "gerber/unzipped_bundles/$id");
        $this->handleResponse($response, 'Could not retrieve unzipped bundle');
        return UnzippedBundle::fromResponse($response);
    }

    private function waitForBundle(string $auth, string $bundleId): Bundle
    {
        $response = $this->waitFor($auth, "gerber/bundles/{$bundleId}");
        $this->handleResponse($response, 'Error while waiting for bundle to be unzipped');
        return Bundle::fromResponse($response);
    }

    /**
     * Create bundle with correct mapping.
     */
    private function createMappedBundle(string $auth, Bundle $bundle): Bundle
    {
        $payload = [
            'filename' => $bundle->getFilename(),
            'layers_urls' => $this->gerbersConverter->createMapping($bundle),
            'names_urls' => $bundle->getNamesUrls()
        ];

        $response = $this->pcbNgHttpClient->request(
            'POST',
            'gerber/bundles',
            [
                'headers' => $this->getHeaders($auth),
                'json' => $payload,
            ]);

        $this->handleResponse($response, 'Could not map bundle');
        return Bundle::fromResponse($response);
    }

    private function waitForPcbPriceQuote(string $auth,
                                          string $bundleId): PcbPriceQuote
    {
        $response = $this->waitFor($auth, "gerber/pcb_price_quotes/$bundleId");
        $this->handleResponse($response, 'Could not retrieve PCB price quote');
        return PcbPriceQuote::fromResponse($response);
    }

    /**
     * @throws PcbNgClientException
     */
    private function postPnp(string $auth,
                             string $url): PostPnpResponse
    {
        $payload = [
            'file_url' => $url,
        ];

        $response = $this->pcbNgHttpClient->request(
            'POST',
            'bom/pnp_files',
            [
                'headers' => $this->getHeaders($auth),
                'json' => $payload,
            ]);

        $this->handleResponse($response, 'Could not post PNP file');
        return PostPnpResponse::fromResponse($response);
    }

    /**
     * @throws PcbNgClientException
     */
    private function waitForPnpPreview(string $auth,
                                       string $id): PnpReview
    {
        $response = $this->waitFor($auth, "bom/pnp_file_previews/{$id}");
        $this->handleResponse($response, 'Could not retrieve PNP preview');
        return PnpReview::fromResponse($response);
    }

    /**
     * @param string[] $pnpMapping
     * @throws PcbNgClientException
     */
    private function newPnpParsing(string $auth,
                                   string $pnpFileUrl,
                                   array $pnpMapping): NewPnpParsingResponse
    {
        $payload = [
            'file_url' => $pnpFileUrl,
            'options' => [
                'mapping' => $pnpMapping,
            ]
        ];

        $response = $this->pcbNgHttpClient->request(
            'POST',
            'bom/pnp_parsing_options',
            [
                'headers' => $this->getHeaders($auth),
                'json' => $payload,
            ]);

        $this->handleResponse($response, 'Could not post PNP file');
        return NewPnpParsingResponse::fromResponse($response);
    }

    /**
     * @throws PcbNgClientException
     */
    private function waitForPnpData(string $auth, string $id): PnpData
    {
        $response = $this->waitFor($auth, "bom/pnp_data/{$id}");
        $this->handleResponse($response, 'Could not retrieve PNP parsed data');
        return PnpData::fromResponse($response);
    }

    /**
     * @param PcbNgPart[] $items
     * @throws PcbNgClientException
     */
    private function newBomCart(string $auth,
                                array $items,
                                int $kitQuantity,
                                bool $econoEnable): NewBomCartResponse
    {
        $payload = [
            'items' => array_map(function (PcbNgPart $part) {
                return [
                    'refdes' => $part->getIdentifier(),
                    'dknum' => $part->getDigiKeySku(),
                ];
            }, $items),
            'kit-qty' => $kitQuantity,
            'econo_enable' => $econoEnable,
        ];

        $response = $this->pcbNgHttpClient->request(
            'POST',
            'bom/bom_carts',
            [
                'headers' => $this->getHeaders($auth),
                'json' => $payload,
            ]);

        $this->handleResponse($response, 'Could not create new BOM cart');
        return NewBomCartResponse::fromResponse($response);
    }

    /**
     * @throws PcbNgClientException
     */
    private function waitForBomQuote(string $auth, string $id): BomQuote
    {
        $response = $this->waitFor($auth, "bom/bom_quotes/{$id}");
        $this->handleResponse($response, 'Could not retrieve BOM quote');
        return BomQuote::fromResponse($response);
    }

    /**
     * @param PcbNgPart[] $bomItems
     * @throws PcbNgClientException
     */
    public function newBom(string $auth,
                           string $quoteId,
                           string $bundleId,
                           string $pnpDataId,
                           array $bomItems): Bom
    {
        $placement = [];
        foreach ($bomItems as $bomItem) {
            $placement[$bomItem->getIdentifier()] = [
                'x' => $bomItem->getX(),
                'y' => $bomItem->getY(),
                'status' => 'locked',
                'side' => $bomItem->getSide(),
                'rotation' => $bomItem->getRotation(),
            ];
        }

        $payload = [
            'bom_quote_id' => $quoteId,
            'bundle_id' => $bundleId,
            'pnp-data-id' => $pnpDataId,
            'placement' => $placement,
        ];

        $response = $this->pcbNgHttpClient->request(
            'POST',
            'bom/boms',
            [
                'headers' => $this->getHeaders($auth),
                'json' => $payload,
            ]);

        $this->handleResponse($response, 'Could not retrieve BOM quote');
        return Bom::fromResponse($response);
    }

    /**
     * @throws PcbNgClientException
     */
    public function newBoard(string $auth,
                             string $bundleId,
                             string $bomId): Board
    {
        // Must confirm DFM checks;
        $dfmCheckPayload = [
            'ignored' => [],
        ];

        $dfmCheckResponse = $this->pcbNgHttpClient->request(
            'PUT',
            "gerber/pcb_dfm_options/$bundleId",
            [
                'headers' => $this->getHeaders($auth),
                'json' => $dfmCheckPayload,
            ]);

        $this->handleResponse($dfmCheckResponse, 'DFM option update failed');


        // Create a board.
        $createBoardPayload = [
            'bundle_id' => $bundleId,
            'bom_id' => $bomId,
        ];

        $createBoardResponse = $this->pcbNgHttpClient->request(
            'POST',
            "boards",
            [
                'headers' => $this->getHeaders($auth),
                'json' => $createBoardPayload,
            ]);

        $this->handleResponse($createBoardResponse, 'Board creation failed');
        return Board::fromResponse($createBoardResponse);
    }

    /**
     * @throws PcbNgClientException
     */
    private function waitForDfmReports(string $auth,
                                       string $bundleId): DfmReports
    {
        $response = $this->waitFor($auth, "gerber/pcb_dfm_reports/$bundleId");
        $this->handleResponse($response, 'Could not retrieve DFM reports');
        return DfmReports::fromResponse($response);
    }


    /**
     * @throws PcbNgClientException
     */
    public function newUserBoard(string $auth,
                                 string $name,
                                 ?string $boardId): UserBoard
    {
        $payload = [
            'name' => $name,
            'board_id' => $boardId,
        ];

        $response = $this->pcbNgHttpClient->request(
            'POST',
            'userboards',
            [
                'headers' => $this->getHeaders($auth),
                'json' => $payload,
            ]);

        $this->handleResponse($response, 'User Board creation Failed');
        return UserBoard::fromResponse($response);
    }

    /**
     * @throws PcbNgClientException
     */
    private function newRfqs(string $auth,
                             string $boardId,
                             string $boardName,
                             int $quantity,
                             string $address,
                             string $tier): NewRfqsResponse
    {
        $payload = [
            'board_id' => $boardId,
            'board_name' => $boardName,
            'order_type' => 'pcb-and-assembly',
            'quantity' => $quantity,
            'community_code' => null,
            'shipping_address' => [
                'address-country' => $address,
            ],
            'econo_enable' => true,
            'service_tier' => $tier,
        ];

        $response = $this->pcbNgHttpClient->request(
            'POST',
            "order_rfqs",
            [
                'headers' => $this->getHeaders($auth),
                'json' => $payload,
            ]);

        $this->handleResponse($response, 'Could not create RFQS');
        return NewRfqsResponse::fromResponse($response);
    }

    /**
     * @throws PcbNgClientException
     */
    private function waitForQuotes(string $auth,
                                   string $rfqsId): Quotes
    {
        $response = $this->waitFor($auth, "order_quotes/{$rfqsId}");
        $this->handleResponse($response, 'Failed to retrieve order quotes');
        return Quotes::fromResponse($response);
    }

    /**
     * @throws PcbNgClientException
     */
    private function postOrder(string $auth,
                               string $rfqsId,
                               string $userBoardId,
                               string $stripeToken): PostOrderResponse
    {
        $payload = [
            'order_rfq_id' => $rfqsId,
            'userboard_id' => $userBoardId,
            'shipping_address' => ['address-country' => 'US'],
            'stripe_token' => $stripeToken,
            'require_order_invoice' => False,
        ];

        $response = $this->pcbNgHttpClient->request(
            'POST',
            "orders",
            [
                'headers' => $this->getHeaders($auth),
                'json' => $payload,
            ]);

        $this->handleResponse($response, 'Could not create a new order');
        return PostOrderResponse::fromResponse($response);
    }

    /**
     * @throws PcbNgClientException
     */
    private function getOrderStatus(string $auth, string $id): OrderStatus
    {
        $response = $this->pcbNgHttpClient->request(
            'GET',
            "order_statuses/{$id}",
            [
                'headers' => $this->getHeaders($auth),
            ]);
        $this->handleResponse($response, 'Could not retrieve order status');
        return OrderStatus::fromResponse($response);
    }

    /**
     * @throws PcbNgClientException
     */
    public function uploadGerbers(string $auth,
                                  string $filename,
                                  string $gerbersZipData): Bundle
    {
        // Upload all gerber files as one zipped bundle and wait for it to be unzipped.
        $zipBundleUri = $this->uploadFileData($auth, $gerbersZipData, 'application/zip');
        $zippedBundle = $this->postBundleForUnzipping($auth, $zipBundleUri, $filename);
        $zippedBundleId = $zippedBundle->getId();
        // Wait for bundle to be unzipped by backend.
        $zippedBundle = $this->waitForUnzippedBundle($auth, $zippedBundleId);

        $bundle = $this->waitForBundle($auth, $zippedBundle->getBundleId());
        $bundle = $this->createMappedBundle($auth, $bundle);

        return $bundle;

    }

    public function getPcbPriceQuote(string $auth, Bundle $bundle): PcbPriceQuote
    {
        return $this->waitForPcbPriceQuote($auth, $bundle->getId());
    }

    /**
     * @throws PcbNgClientException
     */
    public function uploadPnp(string $auth,
                              string $locationsCsvData): PnpData
    {
        // Upload the PNP file and wait for preview to be generated by backend.
        $pnpFileUrl = $this->uploadFileData($auth, $locationsCsvData, 'text/plain');
        $pnp = $this->postPnp($auth, $pnpFileUrl);
        $pnpId = $pnp->getId();
        // Wait for pnp file to be processed by backend.
        $pnpPreview = $this->waitForPnpPreview($auth, $pnpId);
        $pnpMapping = $pnpPreview->getOptions()['mapping'];


        // Create new PNP data based on PNP parsing and mapping and wait for data.
        $pnpParsing = $this->newPnpParsing($auth, $pnpFileUrl, $pnpMapping);
        $pnpDataId = $pnpParsing->getId();
        return $this->waitForPnpData($auth, $pnpDataId);
    }

    /**
     * @throws PcbNgClientException
     */
    public function getBomQuote(string $auth, int $orderQuantity, PnpData $pnpData): BomQuote
    {
        // Create new BOM cart and wait for quotes.
        // NOTE: Order quantity will affect the bom quote (subtotal), but the
        // quantity on the PCB:NG frontend will always default to 6.
        $bomCart = $this->newBomCart($auth, $pnpData->getParts(), $orderQuantity, true);
        $bomCartId = $bomCart->getId();
        return $this->waitForBomQuote($auth, $bomCartId);
    }


    /**
     * @throws PcbNgClientException
     */
    public function uploadBuildFilesAndCreateUserBoard(string $boardName,
                                                       string $gerbersZipData,
                                                       string $locationsCsvData,
                                                       int $orderQuantity): UserBoard
    {
        // Receive authorization token.
        $auth = $this->getAuth();


        $bundle = $this->uploadGerbers($auth, "$boardName - gerbers.zip", $gerbersZipData);
        $bundleId = $bundle->getId();


        $pnpData = $this->uploadPnp($auth, $locationsCsvData);
        $pnpDataId = $pnpData->getId();


        $bomQuote = $this->getBomQuote($auth, $orderQuantity, $pnpData);
        $bomQuoteId = $bomQuote->getId();


        // Create new BOM document.
        $bom = $this->newBom($auth, $bomQuoteId, $bundleId, $pnpDataId, $pnpData->getParts());
        $bomId = $bom->getId();


        // Create new board based on BOM.
        $board = $this->newBoard($auth, $bundleId, $bomId);
        $boardId = $board->getId();
        $userBoard = $this->newUserBoard($auth, $boardName, $boardId);
        return $userBoard;
    }

    /**
     * CODE PORTED FROM PYTHON SAMPLE CODE, USE THIS AS REFERENCE.
     */
    public function order(): OrderStatus
    {
        $boardName = 'New Board Example';
        $boardQuantity = 6;
        $boardFile = '/home/john/Downloads/demo_555.zip'; // Gerbers
        $pnpFile = '/home/john/Downloads/demo_555_xyrs.csv'; // locations CSV

        // Receive authorization token.
        $auth = $this->getAuth();

        // Upload all gerber files as one zipped bundle and wait for it to be unzipped.
        $zipBundleUri = $this->uploadFile($auth, $boardFile, 'application/zip');
        $zippedBundle = $this->postBundleForUnzipping($auth, $zipBundleUri, 'demo_555.zip');
        $zippedBundleId = $zippedBundle->getId();
        // Wait for bundle to be unzipped by backend.
        $bundle = $this->waitForUnzippedBundle($auth, $zippedBundleId);
        $bundleId = $bundle->getBundleId();
        $this->waitForBundle($auth, $bundleId);

        // Upload the PNP file and wait for preview to be generated by backend.
        $pnpFileUrl = $this->uploadFile($auth, $pnpFile, 'text/plain');
        $pnp = $this->postPnp($auth, $pnpFileUrl);
        $pnpId = $pnp->getId();
        // Wait for pnp file to be processed by backend.
        $pnpPreview = $this->waitForPnpPreview($auth, $pnpId);
        $pnpMapping = $pnpPreview->getOptions()['mapping'];

        // Create new PNP data based on PNP parsing and mapping and wait for data.
        $pnpParsing = $this->newPnpParsing($auth, $pnpFileUrl, $pnpMapping);
        $pnpDataId = $pnpParsing->getId();
        $pnpData = $this->waitForPnpData($auth, $pnpDataId);

        // Create new BOM cart and wait for quotes.
        // NOTE: Board quantity will affect the bom quote (subtotal), but the
        // quantity on the PCB:NG frontend will always default to 6.
        $bomCart = $this->newBomCart($auth, $pnpData->getParts(), $boardQuantity, true);
        $bomCartId = $bomCart->getId();
        $bomQuote = $this->waitForBomQuote($auth, $bomCartId);
        $bomQuoteId = $bomQuote->getId();

        // Create new BOM document.
        $bom = $this->newBom($auth, $bomQuoteId, $bundleId, $pnpDataId, $pnpData->getParts());
        $bomId = $bom->getId();

        // Create new board based on BOM.
        $board = $this->newBoard($auth, $bundleId, $bomId);
        $boardId = $board->getId();
        $userBoard = $this->newUserBoard($auth, $boardName, $boardId);
        $userBoardId = $userBoard->getId();

        // Wait for dfm reports (needed for RFQS to work properly).
        $this->waitForDfmReports($auth, $bundleId);

        // Create order RFQS.
        $rfqs = $this->newRfqs(
            $auth,
            $boardId,
            $boardName,
            $boardQuantity,
            'US',
            'std');
        $rfqsId = $rfqs->getId();

        $quotes = $this->waitForQuotes($auth, $rfqsId);

        // Post order and print status.
        $stripeToken = '1234567890';
        $order = $this->postOrder($auth, $rfqsId, $userBoardId,$stripeToken);
        $orderId = $order->getId();
        $status = $this->getOrderStatus($auth, $orderId);

        return $status;
    }
}