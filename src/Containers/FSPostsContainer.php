<?php

namespace Miakiwi\Kiwiquill\Containers;

use Exception;
use Framework\Logger;
use Miakiwi\Kiwiquill\Exceptions\PostsContainerCreationException;
use Miakiwi\Kiwiquill\Exceptions\PostsContainerWriteException;
use Miakiwi\Kiwiquill\Models\Post;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;



class FSPostsContainer implements PostsContainerInterface
{
    /**
     * The directory where the posts are stored.
     * @var string
     */
    protected string $directory;

    /**
     * The file where the index of posts is stored.
     * @var string
     */
    private string $index_file;



    public function __construct(string $directory, ?string $index_file = null)
    {
        // Set the directory where the posts are stored.
        $this->setDirectory($directory);



        // Set the file where the index of posts is stored.
        $this->setIndexFile($index_file);
    }



    /**
     * Set the path to the index file.
     * @param mixed $index_file The path to the index file, or null to use the default 'index.json' at the root of the posts directory.
     * @return void
     */
    public function setIndexFile(?string $index_file): void
    {
        // If no index file is provided, use 'index.json' at the root of the posts directory.
        if (is_null($index_file)) {
            $this->index_file = '^index.json'; // '^' indicates the root of the posts directory.
        } else {
            // Otherwise, use the provided index file path.
            $this->index_file = $index_file;
        }
    }



    /**
     * Get the path to the index file.
     * @return array|string|null The path to the index file, or null if not set.
     */
    public function getIndexFile(): string
    {
        // Return the path to the index file.
        return preg_replace("/^(\^)/mi", $this->getDirectory(), $this->index_file); // Remove the '^' prefix if it exists.
    }



    /**
     * Get the index of posts from the index file.
     * @return array|null The index of posts as an associative array, or null if the index file does not exist or is invalid.
     */
    public function getIndex(): ?array
    {
        // Create the index file if it does not exist.
        $this->createIndex();



        // Read the index file and decode its JSON content.
        $indexContent = file_get_contents($this->getIndexFile());



        if ($indexContent === false) {
            return null; // Return null if the index file cannot be read.
        }

        $index = json_decode($indexContent, true);



        if (json_last_error() !== JSON_ERROR_NONE) {
            return null; // Return null if the JSON content is invalid.
        }



        // Return the decoded index array.
        return $index ?: null; // Return null if the index is empty.
    }



    /**
     * Writes to the index file.
     * @param array $index The index of posts to write to the index file.
     * @throws \Miakiwi\Kiwiquill\Exceptions\PostsContainerWriteException if the index file cannot be written.
     * @return void
     */
    public function setIndex(array $index): void
    {
        // Create the index file if it does not exist.
        $this->createIndex();



        // Save the index to the index file.
        if (file_put_contents($this->getIndexFile(), json_encode($index, JSON_PRETTY_PRINT)) === false) {
            throw new PostsContainerWriteException("Failed to write index file: " . $this->getIndexFile());
        }
    }



    /**
     * Get the path of a post file relative to the posts directory.
     * @param string $path The absolute path to the post file.
     * @throws \InvalidArgumentException if the provided path is not valid or not in the posts directory.
     * @return string The relative path to the post file, starting with a directory separator.
     */
    public function getFsPathRelativeToDirectory(string $path): string
    {
        // Ensure the path is absolute.
        $absolutePath = realpath($path);

        // If the path is not absolute, throw an exception.
        if ($absolutePath === false) {
            throw new \InvalidArgumentException("The provided path is not valid: $path");
        }



        // Remove the directory part from the absolute path to get the relative path.
        $relativePath = str_replace($this->getDirectory(), '', $absolutePath);



        // If the path isn't in the directory, throw an exception.
        if ($relativePath === $absolutePath || !str_starts_with($absolutePath, $this->getDirectory())) {
            throw new \InvalidArgumentException("The provided path is not in the posts directory: $path");
        }



        // Ensure the relative path starts with a directory separator.
        if (!str_starts_with($relativePath, DIRECTORY_SEPARATOR)) {
            $relativePath = DIRECTORY_SEPARATOR . $relativePath;
        }



        // Return the relative path to the post file.
        return $relativePath;
    }



    /**
     * Set and create the directory where the posts are stored.
     * @param string $directory The directory where the posts are stored.
     * @throws \Miakiwi\Kiwiquill\Exceptions\PostsContainerCreationException if the directory cannot be created.
     * @return void
     */
    public function setDirectory(string $directory): void
    {
        // Ensure the directory path ends with a directory separator.
        if (!str_ends_with($directory, DIRECTORY_SEPARATOR)) {
            $directory .= DIRECTORY_SEPARATOR;
        }



        // Check if the path to the directory is valid (not that it exists, but that it can at least be created).
        if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
            throw new PostsContainerCreationException("Failed to create posts directory: $directory");
        }



        // Set the directory where the posts are stored.
        $this->directory = $directory;
    }



    /**
     * Get the directory where the posts are stored.
     * @return string The directory where the posts are stored.
     */
    public function getDirectory(): string
    {
        // Return the directory where the posts are stored.
        return $this->directory;
    }



    /**
     * Create the index file if it does not exist.
     * @throws \Miakiwi\Kiwiquill\Exceptions\PostsContainerWriteException if the index file cannot be written.
     * @return void
     */
    public function createIndex(): void
    {
        // Get the path to the index file.
        $indexFile = $this->getIndexFile();



        // If the index file does not exist, create it.
        if (!file_exists($indexFile)) {
            Logger::get()->debug("Creating index file for posts container at: " . $this->getIndexFile());


            // Create an empty index array.
            $index = [];

            // Save the empty index to the index file.
            if (file_put_contents($indexFile, json_encode($index, JSON_PRETTY_PRINT)) === false) {
                throw new PostsContainerWriteException("Failed to write index file: " . $indexFile);
            }
        }
    }



    /**
     * Get the URL path to a post file from its filesystem path.
     * @param string $fs_path The absolute filesystem path to the post file.
     * @return string The URL path to the post file, relative to the posts directory.
     */
    public function getUrlPathFromFsPath(string $fs_path): string
    {
        // Get the relative path to the post file from the posts directory.
        $relativePath = $this->getFsPathRelativeToDirectory($fs_path);

        // Remove the file extension from the relative path.
        $relativePathWithoutExtension = preg_replace('/\.[^.]+$/', '', $relativePath);

        // Convert the relative path to a URL path by replacing directory separators with slashes.
        $urlPath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePathWithoutExtension);

        // Remove leading slashes from the URL path.
        $urlPath = ltrim($urlPath, '/');

        // Return the URL path to the post file.
        return $urlPath;
    }



    /**
     * Get the path to a post indexed by its type and value.
     * @param string $index_type The type of index to search for (e.g., 'id', 'title', 'author', etc.).
     * @param string $index_value The value of the index to search for (e.g., the ID, title, author name, etc.).
     * @return string|null The path to the post if found, or null if not indexed.
     */
    public function getIndexedPostFsPath(string $index_type, string $index_value): ?string
    {
        // Create the index file if it does not exist.
        $this->createIndex();



        // Get the index of posts.
        $index = $this->getIndex();

        if (is_null($index)) {
            return null; // Return null if the index is not available.
        }



        // Check if the post is indexed with the specified type and value.
        if (isset($index[$index_type]) && isset($index[$index_type][$index_value])) {
            $indexedPath = $index[$index_type][$index_value];

            // Check if the path is valid.
            if (is_string($indexedPath) && file_exists($indexedPath)) {
                return $indexedPath; // Return the full path to the indexed post.
            } else {
                // Remove the invalid entry from the index.
                unset($index[$index_type][$index_value]);

                // Save the updated index back to the index file.
                $this->setIndex($index);
            }
        }



        return null;
    }



    /**
     * Index a post by its type and value.
     * @param string $index_type The type of index to create (e.g., 'id', 'title', 'author', etc.).
     * @param string $index_value The value of the index to create (e.g., the ID, title, author name, etc.).
     * @param string $path The path to the post file to index.
     * @return void
     */
    public function indexPost(string $index_type, string $index_value, string $path): void
    {
        // Get the current index of posts.
        $index = $this->getIndex() ?? [];



        // Ensure the index type exists in the index.
        if (!isset($index[$index_type])) {
            $index[$index_type] = [];
        }



        // Add the post path to the index under the specified type.
        $index[$index_type][$index_value] = $path;

        // Save the updated index back to the index file.
        $this->setIndex($index);
    }



    public function getPosts(): array
    {
        // Get all the posts in the posts directory recursively.
        $posts = [];

        $rri = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($rri as $file) {
            // If the file starts with a dot or doesn't end with '.md', skip it.
            if (str_starts_with($file->getFilename(), '.') || !str_ends_with(strtolower($file->getFilename()), '.md')) {
                continue;
            }

            // Add the post to the array.
            try {

                $post = new Post(
                    $this->getUrlPathFromFsPath($file->getPathname()),
                    file_get_contents($file->getPathname())
                );

                // Add the FS path to the post metadata
                $post?->metadata?->add('secret_fs_path', $file->getPathname());

                $posts[] = $post;

            } catch (Exception $e) {

                // Log the error if the post cannot be created.
                Logger::get()->error("Failed to create post from file: " . $file->getPathname(), [
                    'container' => static::class,
                    'exception' => $e,
                    'file' => $file->getPathname()
                ]);

            }
        }



        // Return the array of posts.
        return $posts;
    }



    public function getPostById(string $id): ?Post
    {
        // Check if the post is indexed.
        $indexedPath = $this->getIndexedPostFsPath('id', $id);

        if ($indexedPath) {
            // Add the post to the array.
            return new Post(
                $this->getUrlPathFromFsPath($indexedPath),
                file_get_contents($indexedPath)
            );
        }

        Logger::get()->debug("Post with ID '$id' not found in index, searching in posts directory.");



        // If the post is not indexed, search for it in the posts directory.
        $posts = $this->getPosts();

        foreach ($posts as $post) {
            // If the post's ID matches the provided ID, return the post.
            if ($post?->metadata?->getId() === $id) {
                // Index the post for future reference.
                $this->indexPost('id', $id, $post?->metadata?->get('secret_fs_path'));
                return $post;
            }
        }



        // If no post with the specified ID is found, return null.
        return null;
    }



    public function getPostByPath(string $path): ?Post
    {
        // Check if the post is indexed.
        $indexedPath = $this->getIndexedPostFsPath('path', $path);

        if ($indexedPath) {
            // Add the post to the array.
            return new Post(
                $this->getUrlPathFromFsPath($indexedPath),
                file_get_contents($indexedPath)
            );
        }



        // If the post is not indexed, search for it in the posts directory.
        $posts = $this->getPosts();

        foreach ($posts as $post) {
            // If the post's path matches the provided path, return the post.
            if ($post->getPath() === $path) {
                // Index the post for future reference.
                $this->indexPost('path', $path, $post?->metadata?->get('secret_fs_path'));
                return $post;
            }
        }



        // If no post with the specified path is found, return null.
        return null;
    }



    public function getPostsByTags(array $tags): array
    {
        $matchingPosts = [];



        // Get all posts in the container.
        $posts = $this->getPosts();

        foreach ($posts as $post) {
            // Check if the post has any of the specified tags.
            foreach ($tags as $tag) {
                if (in_array($tag, $post?->metadata?->get('tags', []))) {
                    // If a matching tag is found, add the post to the results.
                    $matchingPosts[] = $post;
                    break; // No need to check other tags for this post.
                }
            }
        }

        // Return the array of posts that match the specified tags.
        return $matchingPosts;
    }



    public function getPostsByTitle(string $title): array
    {
        $matchingPosts = [];



        // Get all posts in the container.
        $posts = $this->getPosts();

        foreach ($posts as $post) {
            // Check if the post's title matches the specified title.
            if (strtolower($post?->metadata?->getTitle() ?? '') === strtolower($title)) {
                // If a matching title is found, add the post to the results.
                $matchingPosts[] = $post;
            }
        }

        // Return the array of posts that match the specified title.
        return $matchingPosts;
    }



    public function getPostsByAuthor(string $author): array
    {
        $matchingPosts = [];



        // Get all posts in the container.
        $posts = $this->getPosts();

        foreach ($posts as $post) {
            // Check if the post's author matches the specified author.
            if (strtolower($post?->metadata?->getAuthor() ?? '') === strtolower($author)) {
                // If a matching author is found, add the post to the results.
                $matchingPosts[] = $post;
            }
        }

        // Return the array of posts that match the specified author.
        return $matchingPosts;
    }



    public function getPostsByPublicationDate(\DateTime $date): array
    {
        $matchingPosts = [];



        // Get all posts in the container.
        $posts = $this->getPosts();

        foreach ($posts as $post) {
            // Check if the post's publication date matches the specified date.
            $publicationDate = $post?->metadata?->getPublicationDate();

            if ($publicationDate && $publicationDate === $date->format('Y-m-d')) {
                // If a matching publication date is found, add the post to the results.
                $matchingPosts[] = $post;
            }
        }

        // Return the array of posts that match the specified publication date.
        return $matchingPosts;
    }



    public function getPostsByUpdateDate(\DateTime $date): array
    {
        $matchingPosts = [];



        // Get all posts in the container.
        $posts = $this->getPosts();

        foreach ($posts as $post) {
            // Check if the post's update date matches the specified date.
            $updateDate = $post?->metadata?->getUpdateDate();

            if ($updateDate && $updateDate === $date->format('Y-m-d')) {
                // If a matching update date is found, add the post to the results.
                $matchingPosts[] = $post;
            }
        }

        // Return the array of posts that match the specified update date.
        return $matchingPosts;
    }



    public function getTags(): array
    {
        // Get all the posts in the container.
        $posts = $this->getPosts();



        // Initialize an array to hold unique tags.
        $tags = [];

        foreach ($posts as $post) {
            foreach ($post->metadata->getTags() as $tag) {
                // Add the tag to the array if it is not already present.
                if (!in_array($tag, $tags)) {
                    $tags[] = $tag;
                }
            }
        }

        // Sort the tags alphabetically.
        sort($tags);



        // Return the array of unique tags.
        return $tags;
    }



    public function tagExists(string $tag): bool
    {
        // Get all the tags in the container.
        $tags = $this->getTags();



        return in_array($tag, $tags); // Check if the specified tag exists in the tags array.
    }
}