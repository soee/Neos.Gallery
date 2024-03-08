<?php
namespace Soee\Gallery\DataSources;

use Neos\Utility\TypeHandling;
use Neos\Flow\Annotations as Flow;
use Neos\Media\Domain\Model\AssetCollection;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Media\Domain\Repository\AssetCollectionRepository;

/**
 * Class AssetCollectionDataSource
 * @package Soee\Gallery\DataSources
 */
class AssetCollectionDataSource extends AbstractDataSource
{
    /**
     * @var string
     */
    static protected $identifier = 'soee-gallery-assetcollections';

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var AssetCollectionRepository
     */
    protected $assetCollectionRepository;


    public function getData(Node $node = null, array $arguments = []): array
    {
        $options = [['label' => '---', 'value' => '']];
        $assetCollections = $this->assetCollectionRepository->findAll();
        foreach ($assetCollections as $assetCollection) {
            /** @var AssetCollection $assetCollection */
            $options[] = [
                'label' => $assetCollection->getTitle(),
                'value' => json_encode([
                    '__identity' => $this->persistenceManager->getIdentifierByObject($assetCollection),
                    '__type' => TypeHandling::getTypeForValue($assetCollection)
                ])
            ];
        }

        return $options;
    }
}

