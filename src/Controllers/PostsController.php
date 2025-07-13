<?php

namespace Miakiwi\Kiwiquill\Controllers;

use Framework\Logger;
use MiaKiwi\Kaphpir\ApiResponse\HttpApiResponse;
use MiaKiwi\Kaphpir\Responses\v25_1_0\Response;
use MiaKiwi\Kaphpir\ResponseSerializer\JsonSerializer;
use Miakiwi\Kiwiquill\Containers\FSPostsContainer;
use Miakiwi\Kiwiquill\Exceptions\InvalidPaginationParametersError;
use Miakiwi\Kiwiquill\Exceptions\PostNotFoundError;
use Miakiwi\Kiwiquill\Exceptions\PostsContainerNotFoundError;



class PostsController
{
    /**
     * Handles the request to retrieve all posts.
     * @return never
     */
    public function index(): never
    {
        Logger::get()->debug("Initializing controller method", [
            'controller' => static::class,
            'method' => 'index'
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



        // Handle pagination if needed.
        $get = array_change_key_case($_GET, CASE_LOWER);

        $offset = max(0, isset($get['offset']) ? (int) $get['offset'] : 0);
        $limit = max(1, min(100, isset($get['limit']) ? (int) $get['limit'] : 100));
        $total = count($posts);

        if ($offset < 0 || $limit <= 0) {
            HttpApiResponse::send(
                JsonSerializer::getInstance(),
                (new Response())->error(new InvalidPaginationParametersError())->message("Invalid pagination parameters.")
            );
        }

        // Prepare the pagination metadata.
        $pagination = [
            'offset' => $offset,
            'limit' => $limit,
            'total' => $total,
            'has_more' => ($offset + $limit) < $total,
            'next_offset' => ($offset + $limit) < $total ? $offset + $limit : null,
            'previous_offset' => $offset > 0 ? max(0, $offset - $limit) : null
        ];

        // Slice the posts array for pagination.
        $paginated_posts = array_slice($posts, $offset, $limit);



        // Convert the posts to their KAPIR value representation.
        $data = array_map(function ($post) {
            return $post->getKapirValue();
        }, $paginated_posts);



        // Send the response with the posts.
        HttpApiResponse::send(
            JsonSerializer::getInstance(),
            (new Response())->success()->data($data)->message("Posts retrieved successfully!")->metadata(['pagination' => $pagination])
        );



        // End the script execution.
        die();
    }



    /**
     * Handles the request to retrieve a post by its path.
     * @param string $path The path of the post to retrieve.
     * @return never
     */
    public function show(string $path): never
    {
        Logger::get()->debug("Initializing controller method", [
            'controller' => static::class,
            'method' => 'show'
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



        // Get the post by its path.
        $post = $container->getPostByPath($path);



        // If the post is not found, return an error.
        if (!$post) {
            HttpApiResponse::send(
                JsonSerializer::getInstance(),
                (new Response())->error(new PostNotFoundError("Post with path '$path' not found."))->message("Post not found.")
            );

            die();
        }



        // Return the post data.
        HttpApiResponse::send(
            JsonSerializer::getInstance(),
            (new Response())->success()->data($post)->message("Post retrieved successfully!")
        );



        // End the script execution.
        die();
    }



    /**
     * Handles the request to retrieve metadata for a post by its path.
     * @param string $path The path of the post to retrieve metadata for.
     * @return never
     */
    public function metadata(string $path): never
    {
        Logger::get()->debug("Initializing controller method", [
            'controller' => static::class,
            'method' => 'metadata'
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



        // Get the post by its path.
        $post = $container->getPostByPath($path);



        // If the post is not found, return an error.
        if (!$post) {
            HttpApiResponse::send(
                JsonSerializer::getInstance(),
                (new Response())->error(new PostNotFoundError("Post with path '$path' not found."))->message("Post not found.")
            );

            die();
        }



        // Return the post metadata.
        HttpApiResponse::send(
            JsonSerializer::getInstance(),
            (new Response())->success()->data($post->metadata)->message("Post metadata retrieved successfully!")
        );



        // End the script execution.
        die();
    }



    /**
     * Handles the request to retrieve a post by its ID.
     * @param string $id The ID of the post to retrieve.
     * @return never
     */
    public function showById(string $id): never
    {
        Logger::get()->debug("Initializing controller method", [
            'controller' => static::class,
            'method' => 'showById'
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



        // Get the post by its ID.
        $post = $container->getPostById($id);



        // If the post is not found, return an error.
        if (!$post) {
            HttpApiResponse::send(
                JsonSerializer::getInstance(),
                (new Response())->error(new PostNotFoundError("Post with ID '$id' not found."))->message("Post not found.")
            );

            die();
        }



        // Return the post data.
        HttpApiResponse::send(
            JsonSerializer::getInstance(),
            (new Response())->success()->data($post)->message("Post retrieved successfully!")
        );



        // End the script execution.
        die();
    }



    public function search(): never
    {
        Logger::get()->debug("Initializing controller method", [
            'controller' => static::class,
            'method' => 'search'
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



        // Get the search parameters from the query string.
        $get = array_change_key_case($_GET, CASE_LOWER);

        // Parameters are combined with AND logic.
        $tags = isset($get['tags']) ? explode(',', $get['tags']) : []; // Tags are combined with OR logic.
        $title = isset($get['title']) ? $get['title'] : '';
        $author = isset($get['author']) ? $get['author'] : '';



        Logger::get()->debug("Search parameters", [
            'tags' => $tags,
            'title' => $title,
            'author' => $author
        ]);



        // Get the posts based on tags, title, and author.
        $posts_by_tags = $tags ? $container->getPostsByTags($tags) : null;
        $posts_by_title = $title ? $container->getPostsByTitle($title) : null;
        $posts_by_author = $author ? $container->getPostsByAuthor($author) : null;

        Logger::get()->debug("Posts found by search criteria", [
            'posts_by_tags' => count($posts_by_tags ?? []),
            'posts_by_title' => count($posts_by_title ?? []),
            'posts_by_author' => count($posts_by_author ?? [])
        ]);

        $all_posts = [];

        if ($posts_by_tags) {
            $all_posts = array_merge($all_posts, $posts_by_tags);
        }
        if ($posts_by_title) {
            $all_posts = array_merge($all_posts, $posts_by_title);
        }
        if ($posts_by_author) {
            $all_posts = array_merge($all_posts, $posts_by_author);
        }



        // Get all the posts that are in all of the arrays.
        $posts = [];

        foreach ($all_posts as $post) {
            // Check if the post is in the tags array.
            if ($posts_by_tags && !in_array($post, $posts_by_tags)) {
                continue;
            }

            // Check if the post is in the title array.
            if ($posts_by_title && !in_array($post, $posts_by_title)) {
                continue;
            }

            // Check if the post is in the author array.
            if ($posts_by_author && !in_array($post, $posts_by_author)) {
                continue;
            }

            // If the post passes all checks, add it to the posts array.
            $posts[] = $post;
        }

        $posts = array_unique($posts, SORT_REGULAR);



        // If no posts are found, return an empty array.
        if (empty($posts)) {
            HttpApiResponse::send(
                JsonSerializer::getInstance(),
                (new Response())->success()->data([])->message("No posts found matching the search criteria.")
            );

            die();
        }



        // Handle pagination if needed.
        $offset = max(0, isset($get['offset']) ? (int) $get['offset'] : 0);
        $limit = max(1, min(100, isset($get['limit']) ? (int) $get['limit'] : 100));
        $total = count($posts);

        if ($offset < 0 || $limit <= 0) {
            HttpApiResponse::send(
                JsonSerializer::getInstance(),
                (new Response())->error(new InvalidPaginationParametersError())->message("Invalid pagination parameters.")
            );
        }

        // Prepare the pagination metadata.
        $pagination = [
            'offset' => $offset,
            'limit' => $limit,
            'total' => $total,
            'has_more' => ($offset + $limit) < $total,
            'next_offset' => ($offset + $limit) < $total ? $offset + $limit : null,
            'previous_offset' => $offset > 0 ? max(0, $offset - $limit) : null
        ];

        // Slice the posts array for pagination.
        $paginated_posts = array_slice($posts, $offset, $limit);



        // Convert the posts to their KAPIR value representation.
        $data = array_map(function ($post) {
            return $post->getKapirValue();
        }, $paginated_posts);



        // Send the response with the posts.
        HttpApiResponse::send(
            JsonSerializer::getInstance(),
            (new Response())->success()->data($data)->message("Posts retrieved successfully!")->metadata(['pagination' => $pagination])
        );



        // End the script execution.
        die();
    }
}