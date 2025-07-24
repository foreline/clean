<?php
declare(strict_types=1);

namespace App\Domain\Post\Presentation\Form;

use App\Domain\Post\Aggregate\Post;
use Domain\User\UseCase\GetUser;
use \ReflectionClass;

/**
 * PostForm DTO and Validation
 */
class PostForm
{
    public const ID = 'id';
    public const TITLE = 'title';
    public const CONTENT = 'content';
    public const AUTHOR = 'author';
    public const POST_DATE = 'post_date';
    public const IS_DRAFT = 'is_draft';
    public const CATEGORIES = 'categories';
    public const TAGS = 'tags';
    public const COMMENTS = 'comments';

    /**
     * @param array $data
     * @param ?Post $post
     * @return Post
     * @throws \Exception
     */
    public function convertToEntity(array $data, ?Post $post = null): Post
    {
        $post = $post ?? new Post();

        if (array_key_exists(self::ID, $data) && 0 < (int)$data[self::ID]) {
            $post->setId((int)$data[self::ID]);
        }

        if (array_key_exists(self::TITLE, $data)) {
            $post->setTitle((string)$data[self::TITLE]);
        }

        if (array_key_exists(self::CONTENT, $data)) {
            $post->setContent((string)$data[self::CONTENT]);
        }

        if (array_key_exists(self::AUTHOR, $data)) {
            $author = (new GetUser())->get((int)$data[self::AUTHOR]);
            if ($author) {
                $post->setAuthor($author);
            }
        }

        if (array_key_exists(self::IS_DRAFT, $data)) {
            $post->setDraft((bool)$data[self::IS_DRAFT]);
        }

        if (array_key_exists(self::POST_DATE, $data)) {
            $post->setPostDate(new \DateTime($data[self::POST_DATE]));
        }

        // ... handle categories, tags, comments if needed

        return $post;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function validate(Post $post): void
    {
        // You can use a separate class for complex validation logic
        // (new ValidatePostForm())->validate($post);
        // Or implement validation here
        if (empty($post->getTitle())) {
            throw new \InvalidArgumentException('Post title should not be empty');
        }

        if (strlen($post->getTitle()) > 255) {
            throw new \InvalidArgumentException('Post title is too long (max 255 characters)');
        }

        if (empty($post->getContent())) {
            throw new \InvalidArgumentException('Post content should not be empty');
        }

        if (!$post->getAuthor()) {
            throw new \InvalidArgumentException('Post must have an author');
        }
    }

    /**
     * Returns class constants
     * @return array<string, string>
     */
    public static function getMap(): array
    {
        $reflection = new ReflectionClass(__CLASS__);
        return $reflection->getConstants();
    }
}
