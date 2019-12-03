<?php

namespace Pagekit\Blog\Content;

use Pagekit\Application;
use Pagekit\Content\Event\ContentEvent;
use Pagekit\Event\EventSubscriberInterface;

/**
 * Class ResponsiveImagePlugin
 * @package Pagekit\Blog\Content
 */
class ResponsiveImagePlugin implements EventSubscriberInterface
{

    /**
     * Only process these images.
     *
     * @var string[]
     */
    protected $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    /**
     * What copies of the image need to be created.
     *
     * @var integer[]
     */
    protected $dimensions = [400, 768, 1024];

    /**
     * Content plugins callback.
     *
     * @param ContentEvent $event
     */
    public function onContentPlugins(ContentEvent $event)
    {
        if (!$event['post']) { // Only blog posts
            return;
        }

        $content = $event->getContent();

        if (trim($content) == '') {
            return;
        }

        $document = new \DOMDocument();

        libxml_use_internal_errors(true);

        $document->loadHTML('<?xml encoding="utf-8"?>' . $content);
        $document->encoding = 'utf-8';
        $document->formatOutput = false;

        /** @var \DOMNode $node */
        foreach ($document->getElementsByTagName('img') as $i => $node) {
            $srcValue = $node->getAttribute('src');

            if (!file_exists(implode('/', [Application::get('path'), $srcValue]))) {
                continue;
            }

//            if (stripos($srcValue, '/cache') === 0) // @todo
//            {
//                continue;
//            }

//            $image = Image::open($srcValue);
//            $image->setCacheSystem(new CacheSystem($event['post']));
//            $image->setCacheDir('tmp/temp');

            $node->setAttribute('src', $srcValue . '?1' /*$image->enableProgressive()->jpeg(85)*/);
        }

        // Обрезать сначала и конца строки тэги html и body
        $content = trim(substr($document->saveHTML($document->documentElement), 12, -14));

        $event->setContent($content);
    }

    /**
     * @inheritDoc
     */
    public function subscribe()
    {
        return [
            'content.plugins' => ['onContentPlugins', 10]
        ];
    }

    /**
     * Extracts the filename and extension
     * out of the image path.
     *
     * @throws \InvalidArgumentException
     */
    protected function parseImagePath($imagePath)
    {
        $basename = basename($imagePath);
        $filename = pathinfo($basename, PATHINFO_FILENAME);
        $extension = pathinfo($basename, PATHINFO_EXTENSION);

        if (!in_array($extension, $this->allowedExtensions)) {
            throw new \InvalidArgumentException(sprintf('The specified file type "%s" is not allowed.', $extension));
        }

        return [
            $basename, $filename, $extension
        ];
    }

    /**
     * Returns the hashed file path.
     *
     * @return string
     */
    protected function getPathHash()
    {
        return md5($this->path);
    }

}