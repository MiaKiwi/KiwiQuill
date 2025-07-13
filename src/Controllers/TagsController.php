<?php

namespace Miakiwi\Kiwiquill\Controllers;

use Framework\Logger;
use MiaKiwi\Kaphpir\ApiResponse\HttpApiResponse;
use MiaKiwi\Kaphpir\Responses\v25_1_0\Response;
use MiaKiwi\Kaphpir\ResponseSerializer\JsonSerializer;
use Miakiwi\Kiwiquill\Containers\FSPostsContainer;
use Miakiwi\Kiwiquill\Exceptions\InvalidPaginationParametersError;
use Miakiwi\Kiwiquill\Exceptions\PostsContainerNotFoundError;



class TagsController
{
    /**
     * Handles the request to list all tags.
     * @return never
     */
    public function index(): never
    {
        Logger::get()->debug("Initializing controller method", [
            'controller' => static::class,
            'method' => __METHOD__,
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



        // Get all the tags from the posts container.
        $tags = $container->getTags();



        // Handle pagination if needed.
        $get = array_change_key_case($_GET, CASE_LOWER);

        $offset = max(0, isset($get['offset']) ? (int) $get['offset'] : 0);
        $limit = max(1, min(100, isset($get['limit']) ? (int) $get['limit'] : 100));
        $total = count($tags);

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

        // Slice the array for pagination.
        $paginated_tags = array_slice($tags, $offset, $limit);



        // Send the response with the tags and pagination metadata.
        HttpApiResponse::send(
            JsonSerializer::getInstance(),
            (new Response())->data($paginated_tags)->metadata([
                'pagination' => $pagination
            ])->success()
        );



        // End the script execution.
        die();
    }
}