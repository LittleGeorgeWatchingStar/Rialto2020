<?php

namespace Rialto\Wordpress;

use Gumstix\Wordpress\Model\Post;
use Gumstix\Wordpress\Service\RpcClient;
use Rialto\Stock\ChangeNotice\ChangeNotice;
use Rialto\Stock\ChangeNotice\ChangeNoticeEvent;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\StockEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Creates a blog post every time a stock item or version reaches end-of-life
 * (EOL).
 */
class ChangeNoticeListener implements EventSubscriberInterface
{
    const TITLE_CMS_KEY = 'wordpress.change_notice_title';

    /**
     * The maximum number of stock codes to show in the post title.
     * @var integer
     */
    const MAX_CODES_IN_SUMMARY = 3;

    /** @var RpcClient */
    private $rpcClient;

    /** @var EngineInterface */
    private $templating;

    /**
     * The type of post, eg: "post", "page", etc.
     * @var string
     */
    private $postType;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            StockEvents::CHANGE_NOTICE => 'onChangeNotice',
        ];
    }

    public function __construct(
        RpcClient $rpcClient,
        EngineInterface $templating,
        string $postType = Post::TYPE_POST)
    {
        $this->rpcClient = $rpcClient;
        $this->templating = $templating;
        $this->postType = $postType;
    }

    public function onChangeNotice(ChangeNoticeEvent $event)
    {
        $notice = $event->getNotice();
        if ( $notice->isPublished() ) {
            $this->updatePost($notice);
        } elseif ( $notice->shouldBePublished() ) {
            $this->createPost($notice);
        }
    }

    private function createPost(ChangeNotice $notice)
    {
        $title = $this->getPostTitle($notice);
        $content = $notice->getDescription();

        $post = Post::create($title, $content, $this->postType);
        $post->addTags($this->getTags($notice));

        $postID = $this->rpcClient->newPost($post);
        $notice->setPostID($postID);
    }

    private function getPostTitle(ChangeNotice $notice)
    {
        return $this->templating->render(self::TITLE_CMS_KEY, [
            'stockCodes' => $this->summarizeStockCodes($notice),
        ]);
    }

    private function summarizeStockCodes(ChangeNotice $notice)
    {
        $codes = $this->getTags($notice);
        if ( count($codes) > self::MAX_CODES_IN_SUMMARY ) {
            $codes = array_slice($codes, 0, self::MAX_CODES_IN_SUMMARY);
        }
        return join(', ', $codes);
    }

    private function getTags(ChangeNotice $notice)
    {
        return array_map(function(StockItem $item) {
            return $item->getSku();
        }, $notice->getStockItems());
    }

    private function updatePost(ChangeNotice $notice)
    {
        $post = Post::update($notice->getPostID());
        $post->setTitle($this->getPostTitle($notice));
        $post->addTags($this->getTags($notice));
        $this->rpcClient->editPost($post);
    }
}
