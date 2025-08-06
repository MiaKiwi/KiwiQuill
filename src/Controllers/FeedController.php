<?php

namespace Miakiwi\Kiwiquill\Controllers;

use Framework\Config;
use Framework\Logger;
use Lukaswhite\FeedWriter\RSS2;
use MiaKiwi\Kaphpir\ApiResponse\HttpApiResponse;
use MiaKiwi\Kaphpir\Responses\v25_1_0\Response;
use MiaKiwi\Kaphpir\ResponseSerializer\JsonSerializer;
use Miakiwi\Kiwiquill\Containers\FSPostsContainer;
use Miakiwi\Kiwiquill\Exceptions\PostsContainerNotFoundError;



class FeedController
{
    /**
     * Handles the request to get the global feed.
     * @return never
     */
    public function index(): never
    {
        Logger::get()->debug("Initializing controller method", [
            'controller' => static::class,
            'method' => __FUNCTION__
        ]);



        // Find the container type from the environment variable.
        switch ($_ENV['POSTS_CONTAINER_TYPE']) {
            case 'filesystem':
                $container = new FSPostsContainer($_ENV['POSTS_DIRECTORY']);
                break;

            default:
                // No or unknown container type specified.
                HttpApiResponse::send(
                    JsonSerializer::getInstance(),
                    (new Response())->error(new PostsContainerNotFoundError())->message("Internal server error: Posts container not found.")
                );
        }



        // Get all the posts from the container.
        $posts = $container->getPosts();



        // Create a new RSS feed.
        $feed = new RSS2();



        // Create and populate a global channel.
        $global_channel_config = Config::get("feed.global", []);

        $channel = $feed->addChannel();

        $channel->title($global_channel_config['title'] ?? 'Global Channel')
            ->description($global_channel_config['description'] ?? 'The global channel for this KiwiQuill instance')
            ->link($_ENV['WEB_ROOT'] . 'feed')
            ->lastBuildDate(new \DateTime())
            ->pubDate(new \DateTime())
            ->language($global_channel_config['language'] ?? 'en-US')
            ->addAtomLink($_ENV['WEB_ROOT'] . 'feed', true);



        // Add each post to the channel.
        foreach ($posts as $post) {
            $item = $channel->addItem();

            $post->populateRssItem($item);
        }



        // Return the feed as a response.
        header('Content-Type: ' . $feed->getMimeType());

        echo $feed->toString();

        die();
    }



    /**
     * Handles the request to get a feed for a specific tag.
     * @param string $tag The tag to get the feed for.
     * @return never
     */
    public function tag(string $tag): never
    {
        Logger::get()->debug("Initializing controller method", [
            'controller' => static::class,
            'method' => __FUNCTION__
        ]);



        // Find the container type from the environment variable.
        switch ($_ENV['POSTS_CONTAINER_TYPE']) {
            case 'filesystem':
                $container = new FSPostsContainer($_ENV['POSTS_DIRECTORY']);
                break;

            default:
                // No or unknown container type specified.
                HttpApiResponse::send(
                    JsonSerializer::getInstance(),
                    (new Response())->error(new PostsContainerNotFoundError())->message("Internal server error: Posts container not found.")
                );
        }



        // Decode the tag to ensure it's safe for use.
        $tag = urldecode($tag);



        // Get all the posts with that tag from the container.
        $posts = $container->getPostsByTags([$tag]);



        // Create a new RSS feed.
        $feed = new RSS2();



        // Create and populate a channel for the tag.
        $channel = $feed->addChannel();

        $channel->title("Channel for tag: $tag")
            ->description($tag_channel_config['description'] ?? "The channel for the tag: $tag")
            ->link($_ENV['WEB_ROOT'] . "feed/$tag")
            ->lastBuildDate(new \DateTime())
            ->pubDate(new \DateTime())
            ->addAtomLink($_ENV['WEB_ROOT'] . "feed/$tag", true);



        // Add each post to the channel.
        foreach ($posts as $post) {
            $item = $channel->addItem();

            $post->populateRssItem($item);
        }



        // Return the feed as a response.
        header('Content-Type: ' . $feed->getMimeType());

        echo $feed->toString();

        die();
    }
}