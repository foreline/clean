<?php
declare(strict_types=1);

namespace Presentation\Response;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

/**
 *
 */
class ResponseMetaHelper
{
    /**
     * @param int $page page number
     * @param int $pageSize page size
     * @param int $totalCount total count of items
     * @return array
     * @throws InvalidArgumentException
     */
    public static function getMeta(int $page = 1, int $pageSize = 25, int $totalCount = 0): array
    {
        return [
            'pagination'    => [
                'page'      => $page,
                'pageSize'  => $pageSize,
                'pageCount' => ( 0 < $pageSize ? ceil($totalCount / $pageSize) : 0 ),
            ],
            'totalCount'    => $totalCount,
        ];
    }
}