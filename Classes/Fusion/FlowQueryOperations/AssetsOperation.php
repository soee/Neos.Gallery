<?php
declare(strict_types=1);

namespace Soee\Gallery\Fusion\FlowQueryOperations;

use Doctrine\DBAL\Query\QueryBuilder;
use Neos\Eel\FlowQuery\Operations\AbstractOperation;
use Neos\Flow\Annotations as Flow;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Persistence\QueryResultInterface;
use Neos\Media\Domain\Repository\AssetRepository;

/**
 * EEL operation to fetch assets
 *
 * Usage:
 *
 *    ${q(site).assets([selection, [tags], [collections], sortBy, orderBy, limit)}
 */
class AssetsOperation extends AbstractOperation
{

    /**
     * {@inheritdoc}
     *
     * @var string
     */
    static protected $shortName = 'assets';

    /**
     * {@inheritdoc}
     *
     * @var integer
     */
    static protected $priority = 100;

    /**
     * @Flow\Inject
     * @var AssetRepository
     */
    protected AssetRepository $assetRepository;

    /**
     * @Flow\Inject
     * @var ConfigurationManager
     */
    protected ConfigurationManager $configurationManager;

    /**
     * {@inheritdoc}
     *
     * The context doesn't really matter.
     *
     * @param mixed $context
     * @return boolean
     */
    public function canEvaluate($context): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param FlowQuery $flowQuery the FlowQuery object
     * @param array $arguments the arguments for this operation
     * @return mixed
     * @throws \Exception
     */
    public function evaluate(FlowQuery $flowQuery, array $arguments): void
    {
        $selection = $arguments[0] ?? [];
        $tags = $arguments[1] ?? [];
        $collections = $arguments[2] ?? [];
        $sortBy = !empty($arguments[3]) ? $arguments[3] : 'lastModified';
        $orderBy = !empty($arguments[4]) ? $arguments[4] : 'ASC';
        $limit = (int)($arguments[5] ?? 0);

        if (empty($selection) && empty($tags) && empty($collections)) {
            $flowQuery->setContext([]);
            return;
        }
        $excludedMediaTypes = (array)$this->configurationManager->getConfiguration(
            ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
            'Soee.Gallery.album.excludedMediaTypes'
        );

        $assets = $this->findAssets($tags, $collections, $sortBy, $orderBy, $limit, $excludedMediaTypes);

        $flowQuery->setContext(array_values(array_merge($selection, $assets->toArray())));
    }

    protected function findAssets(array $tagList, array $collectionList, string $sortBy, string $orderBy, int $limit, array $excludedMediaTypes = []): QueryResultInterface
    {
        $query = $this->assetRepository->createQuery();
        $queryConstraints = [];

        if (!empty($tagList)) {
            $constraints = [];
            foreach ($tagList as $tag) {
                $constraints[] = $query->contains('tags', $tag);
            }
            $queryConstraints[] = $query->logicalOr($constraints);
        }

        if (!empty($collectionList)) {
            $constraints = [];
            foreach ($collectionList as $collection) {
                $constraints[] = $query->contains('assetCollections', $collection);
            }
            $queryConstraints[] = $query->logicalOr($constraints);
        }

        if (!empty($excludedMediaTypes)) {
            $queryConstraints[] = $query->logicalNot(
                $query->in('resource.mediaType', $excludedMediaTypes)
            );
        }

        $queryConstraints[] = $query->like('resource.mediaType', 'image%');

        $query->matching($query->logicalAnd(...$queryConstraints));
        $query->setOrderings([$sortBy => $orderBy]);

        if ($limit > 0) {
            $query->setLimit($limit);
        }

        // Remove image variants as they are duplicates of normal image assets
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $query->getQueryBuilder();
        $queryBuilder->andWhere('e NOT INSTANCE OF Neos\Media\Domain\Model\ImageVariant');

        return $query->execute();
    }
}
