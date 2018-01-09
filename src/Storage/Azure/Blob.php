<?php

namespace Converter\Storage\Azure;

use Illuminate\Support\Facades\Log;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;
use WindowsAzure\Common\ServiceException;
use WindowsAzure\Common\ServicesBuilder;

class Blob
{
    /**
     * @var \WindowsAzure\Common\WindowsAzure\Blob\Internal\IBlob
     */
    protected $blobInstance;

    /**
     * @var string
     */
    private $containerName;

    /**
     * Blob constructor.
     *
     * @param string $containerName
     * @param bool $devStorage
     */
    public function __construct(string $containerName = 'chargifly', bool $devStorage = false)
    {
        $this->blobInstance = $this->createBlobInstance($devStorage);
        $this->containerName = $containerName;
        $this->validateContainer($this->containerName);
    }

    /**
     * @param bool $devStorage
     *
     * @return \MicrosoftAzure\Storage\Blob\BlobRestProxy
     */
    private function createBlobInstance(bool $devStorage) : \MicrosoftAzure\Storage\Blob\BlobRestProxy
    {
        $connection = [
            'DefaultEndpointsProtocol' => getenv('AZURE_BLOB_DEFAULT_ENDPOINT_PROTOCOL'),
            'AccountName' => getenv('AZURE_BLOB_ACCOUNT_NAME'),
            'AccountKey' => getenv('AZURE_BLOB_ACCOUNT_KEY'),
        ];

        if ($devStorage === true) {
            $connection['UseDevelopmentStorage'] = true;
        }

        return ServicesBuilder::getInstance()->createBlobService(urldecode(http_build_query($connection, '', ';')));
    }

    /**
     * @return \WindowsAzure\Common\WindowsAzure\Blob\Internal\IBlob
     */
    public function getBlobInstance() : \WindowsAzure\Common\WindowsAzure\Blob\Internal\IBlob
    {
        return $this->blobInstance;
    }

    /**
     * @param string $containerName
     * @param string $publicAccessType
     * @param array $containerMeta
     */
    private function createContainer(
        string $containerName = '',
        string $publicAccessType = PublicAccessType::CONTAINER_AND_BLOBS,
        array $containerMeta = []
    ) : void
    {
        if ($containerName === '') {
            $containerName = $this->containerName;
        }
        $containerOptions = new CreateContainerOptions();
        $containerOptions->setPublicAccess($publicAccessType);
        foreach ($containerMeta as $metaindex => $metaValue) {
            $containerOptions->addMetadata($metaindex, $metaValue);
        }
        try {
            $this->blobInstance->createContainer($containerName, $containerOptions);
        } catch (ServiceException $e) {
            Log::error('Microsoft Azure Blob Error: ' . $e->getCode() . ' : ' . $e->getMessage());
        }
    }

    /**
     * @param string $filePath
     * @param string $name
     *
     * @return string
     */
    public function uploadBlob(string $filePath, string $name = '') : string
    {
        try {
            $fileResource = fopen($filePath, 'r');
            if ($name === '') {
                $meta_data = stream_get_meta_data($fileResource);
                $name = $meta_data['uri'];
            }
            $this->blobInstance->createBlockBlob($this->containerName, $name, $fileResource);

            return $this->getBlobUrl($this->containerName, $name);
        } catch (ServiceException $e) {
            Log::error('Microsoft Azure Blob Error: ' . $e->getCode() . ' : ' . $e->getMessage());
        }
    }

    /**
     * @param string $containerName
     *
     * @return array
     */
    public function getBlobs(string $containerName = '') : array
    {
        if ($containerName === '') {
            $containerName = $this->containerName;
        }
        try {
            $blob_list = $this->blobInstance->listBlobs($containerName);

            return $blob_list->getBlobs();
        } catch (ServiceException $e) {
            Log::error('Microsoft Azure Blob Error: ' . $e->getCode() . ' : ' . $e->getMessage());
        }
    }

    /**
     * @param string $containerName
     * @param string $blobName
     */
    public function downloadBlob(string $containerName = '', string $blobName) : void
    {
        if ($containerName === '') {
            $containerName = $this->containerName;
        }
        try {
            $blob = $this->blobInstance->getBlob($containerName, $blobName);
            fpassthru($blob->getContentStream());
        } catch (ServiceException $e) {
            Log::error('Microsoft Azure Blob Error: ' . $e->getCode() . ' : ' . $e->getMessage());
        }
    }

    /**
     * @param string $containerName
     * @param string $blobName
     *
     * @return \MicrosoftAzure\Storage\Blob\Models\Blob
     */
    public function getBlob(string $containerName, string $blobName) : \MicrosoftAzure\Storage\Blob\Models\Blob
    {
        if ($containerName === '') {
            $containerName = $this->containerName;
        }

        return $this->getBlob($containerName, $blobName);
    }

    /**
     * @param string $containerName
     * @param string $blobName
     *
     * @return bool
     */
    public function deleteBlob(string $containerName, string $blobName) : bool
    {
        if ($containerName === '') {
            $containerName = $this->containerName;
        }
        try {
            $this->blobInstance->deleteBlob($containerName, $blobName);

            return true;
        } catch (ServiceException $e) {
            Log::error('Microsoft Azure Blob Error: ' . $e->getCode() . ' : ' . $e->getMessage());
        }
    }

    /**
     * @param string $containerName
     *
     * @return bool
     */
    public function deleteContainer(string $containerName) : bool
    {
        if ($containerName === '') {
            $containerName = $this->containerName;
        }
        try {
            $this->blobInstance->deleteContainer($containerName);

            return true;
        } catch (ServiceException $e) {
            Log::error('Microsoft Azure Blob Error: ' . $e->getCode() . ' : ' . $e->getMessage());
        }
    }

    /**
     * @param string $containerName
     */
    private function validateContainer(string $containerName)
    {
        $containerList = $this->blobInstance->listContainers();
        $containers = $containerList->getContainers();
        $containerExist = false;
        foreach ($containers as $container) {
            if ('/' . $container->getName() === $containerName) {
                $containerExist = true;
            }
        }
        if ($containerExist === false) {
            $this->createContainer($containerName);
        }
    }

    /**
     * @param string $containerName
     * @param string $name
     *
     * @return string
     */
    private function getBlobUrl(string $containerName, string $name) : string
    {
        return sprintf(
            '%s://%s.blob.core.windows.net/%s/%s',
            getenv('AZURE_BLOB_DEFAULT_ENDPOINT_PROTOCOL'),
            getenv('AZURE_BLOB_ACCOUNT_NAME'),
            $containerName,
            $name
        );
    }
}
