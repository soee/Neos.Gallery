<?php
namespace Soee\Gallery\DataSources;

use Neos\Flow\Annotations as Flow;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;

class TagDataSource extends AbstractDataSource
{
    /**
     * @var string
     */
    static protected $identifier = 'soee-gallery-tags';

    /**
     * @Flow\Inject
     * @var \Neos\Flow\Persistence\PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\inject
     * @var \Neos\Media\Domain\Repository\TagRepository
     */
    protected $tagRepository;

    public function getData(Node $node = null, array $arguments = []): array
    {
        $tagCollection = $this->tagRepository->findAll();
        $tags['']['label'] = '---';

        foreach ($tagCollection as $tag) {
            /** @var \Neos\Media\Domain\Model\Tag $tag */
            $tags[$this->persistenceManager->getIdentifierByObject($tag)] = $tag;
        }

        return $tags;
    }
}
