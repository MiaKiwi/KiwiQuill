<?php

namespace Miakiwi\Kiwiquill\Models;

use Lukaswhite\FeedWriter\Entities\Rss\Item;
use Miakiwi\Kiwiquill\PostMetadata;
use MiaKiwi\Kaphpir\IData;
use Miakiwi\Kiwiquill\Slugificator;



class Post implements IData
{
    /**
     * The URL path of the post file.
     * @var string
     */
    protected readonly string $path;

    /**
     * The raw content of the post file.
     * @var string
     */
    protected string $raw_content;

    /**
     * The metadata for the post.
     * @var PostMetadata
     */
    public PostMetadata $metadata;



    /**
     * Instantiate a new Post object.
     * @param string $path The URL path to the post file.
     * @param string $raw_content The raw content of the post file.
     * @param mixed $metadata The metadata for the post.
     */
    public function __construct(string $path, string $raw_content, ?PostMetadata $metadata = null)
    {
        // Set the path to the post file using the slugificator.
        $this->path = Slugificator::slugify($path);

        $this->raw_content = $raw_content;

        $metadata ? $this->metadata = $metadata : $this->detectMetadata();
    }



    /**
     * Get or extract the title of the post.
     * Get the title from the metadata or try to extract it from the raw content.
     * @return string|null
     */
    public function getTitle(): ?string
    {
        // If a title is declared in the metadata, return it.
        if ($this->metadata->getTitle(false)) {
            return $this->metadata->getTitle();
        }



        // Otherwise, try to extract it from the raw content.
        // If the first line is the only line that starts with a hash, use it as the title.
        $lines_starting_with_hash = preg_grep('/^#\s*(.*)$/m', explode("\n", $this->getRawBody()));

        if (count($lines_starting_with_hash) === 1) {
            // Get rid of the hash and return the rest of the line as the title.
            $without_hash = trim(substr($lines_starting_with_hash[0], 1));

            return $without_hash;
        }



        return null;
    }



    /**
     * Get the URL path to the post file.
     * @return string The URL path to the post file as a string.
     */
    public function getPath(): string
    {
        // Return the path to the post file.
        return $this->path;
    }



    /**
     * Get the metadata header from the raw content.
     * @return string The metadata header as a string.
     */
    public function getMetadataHeader(): string
    {
        // Isolate the YAML front matter from the raw content.
        $header = '';

        if (preg_match('/^---\r?\n(.*?)\r?\n---/mis', $this->raw_content, $matches)) {
            $header = $matches[1];
        }

        // Return the header as a string.
        return $header;
    }



    /**
     * Detect, parse, and set the metadata for the post.
     * @return void
     */
    public function detectMetadata(): void
    {
        // Get the metadata from the raw content.
        $header = $this->getMetadataHeader();



        // Create a new PostMetadata object from the header.
        $this->metadata = PostMetadata::parseFromYaml($header);



        // If it isn't already set, set the URL path to the post file in the metadata.
        if (!$this->metadata->get('path', false)) {
            $this->metadata->add('path', $this->getPath());
        }
    }



    /**
     * Get the raw content of the post.
     * @return string The raw content of the post file.
     */
    public function getRawContent(): string
    {
        // Return the raw content of the post.
        return $this->raw_content;
    }



    /**
     * Set the raw content of the post.
     * @param string $raw_content The raw content of the post file.
     * @return void
     */
    public function setRawContent(string $raw_content): void
    {
        // Set the raw content of the post.
        $this->raw_content = $raw_content;
    }



    /**
     * Get the body of the post without the metadata header.
     * @return string The body of the post as a string.
     */
    public function getRawBody(): string
    {
        // Remove the metadata header from the raw content.
        $body = preg_replace('/^(\s*---\r?\n(?:.*?)\r?\n---)/mis', '', $this->raw_content);



        // Trim the body to remove any leading or trailing whitespace.
        $body = trim($body);



        // Return the body as a string.
        return $body;
    }


    // TODO: Implement internal HTML conversion.
    // public function getHtmlBody(): string
    // {
    // }



    /**
     * Populate an RSS item with the post's metadata.
     * @param \Lukaswhite\FeedWriter\Entities\Rss\Item $item The RSS item to populate.
     * @return void
     */
    public function populateRssItem(Item &$item): void
    {
        // Set the title of the post.
        $item->title($this->getTitle() ?? 'Untitled Post');



        // Set the description of the post.
        $item->description($this->metadata->getDescription() ?? 'No description available.');



        // Set the link to the post.
        // Use the ID if it has one, otherwise use the path.
        if ($this->metadata->getId(false)) {
            $item->link($_ENV['WEB_POST_ROOT_ID'] . $this->metadata->getId());

            // Also set the GUID of the item.
            $item->guid($_ENV['WEB_POST_ROOT_ID'] . $this->metadata->getId(), true);
        } else {
            $item->link($_ENV['WEB_POST_ROOT_PATH'] . $this->getPath());
        }



        // Set the publication date of the post.
        if ($this->metadata->getPublicationDate()) {
            $publication_date = new \DateTime($this->metadata->getPublicationDate()->format('Y-m-d H:i:s'));

            $item->pubDate($publication_date);
        }



        // Overwrite the publication date if it was modified.
        if ($this->metadata->getUpdateDate()) {
            $update_date = new \DateTime($this->metadata->getUpdateDate()->format('Y-m-d H:i:s'));

            $item->pubDate($update_date);
        }



        // Set the author of the post.
        if ($this->metadata->getAuthor() && filter_var($this->metadata->getAuthor(), FILTER_VALIDATE_EMAIL)) {
            $item->author($this->metadata->getAuthor());
        }



        // Add a thumbnail if the post has one.
        if ($this->metadata->getImage(false)) {
            $item->addMedia()->url($this->metadata->getImage())->addThumbnail()->url($this->metadata->getImage());
        }
    }



    /**
     * Parse a raw content string into a Post object.
     * @param string $path The URL path to the post file.
     * @param string $raw_content The raw content of the post file.
     * @return Post The Post object with the parsed content and metadata.
     */
    public static function parse(string $path, string $raw_content): static
    {
        // Create a new Post object with the raw content.
        $post = new static($path, $raw_content);



        // Detect and set the metadata for the post.
        $post->detectMetadata();



        // Return the Post object.
        return $post;
    }



    public function getKapirValue(): array
    {
        // Get the post metadata as an array.
        $meta = $this->metadata->getKapirValue();



        // Overwrite the title
        $meta['title'] = $this->getTitle();



        return [
            'raw' => $this->getRawBody(),
            'metadata' => $meta
        ];
    }
}