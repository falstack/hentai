<?php


namespace App\Http\Modules;


use App\Services\Trial\ImageFilter;
use App\Services\Trial\WordsFilter;
use Illuminate\Support\Facades\Redis;
use Mews\Purifier\Facades\Purifier;

class RichContentService
{
    public function preFormatContent(array $data)
    {
        $result = [];
        foreach ($data as $row)
        {
            if ($row['type'] === 'vote')
            {
                $result[] = [
                    'type' => 'vote',
                    'data' => $this->formatVote(
                        $row['data']['items'],
                        $row['data']['right_ids'],
                        $row['data']['max_select'],
                        $row['data']['expired_at']
                    )
                ];
            }
            else
            {
                $result[] = $row;
            }
        }

        return $result;
    }

    public function saveRichContent(array $data)
    {
        $result = [];
        foreach ($data as $row)
        {
            $type = $row['type'];
            if ($type === 'paragraph')
            {
                $text = trim($row['data']['text']);
                if (!$text)
                {
                    continue;
                }
                $result[] = [
                    'type' => $type,
                    'data' => [
                        'text' => Purifier::clean($text)
                    ]
                ];
            }
            else if ($type === 'header')
            {
                $text = trim($row['data']['text']);
                if (!$text)
                {
                    continue;
                }
                $result[] = [
                    'type' => $type,
                    'data' => [
                        'level' => $row['data']['level'],
                        'text' => Purifier::clean($text)
                    ]
                ];
            }
            else if ($type === 'image')
            {
                $text = trim($row['data']['caption']);
                $result[] = [
                    'type' => $type,
                    'data' => array_merge(
                        $row['data'],
                        ['caption' => $text ? Purifier::clean($text) : '']
                    )
                ];
            }
            else if ($type === 'title')
            {
                $text = trim($row['data']['text']);
                $banner = $row['data']['banner'] ?? null;
                if (!$text && !$banner)
                {
                    continue;
                }
                $titleData = [
                    'text' => $text ? Purifier::clean($text) : ''
                ];
                if ($banner)
                {
                    $titleData['banner'] = [
                        'url' => $banner['url'],
                        'width' => $banner['width'],
                        'height' => $banner['height'],
                        'size' => $banner['size'],
                        'mime' => $banner['mime']
                    ];
                }
                $result[] = [
                    'type' => $type,
                    'data' => $titleData
                ];
            }
            else if ($type === 'link')
            {
                $meta = $row['data']['meta'];
                if (!$meta)
                {
                    continue;
                }
                $title = trim($meta['title']);
                $description = trim($meta['description']);

                $result[] = [
                    'type' => $type,
                    'data' => [
                        'link' => $row['data']['link'],
                        'meta' => [
                            'title' => Purifier::clean($title),
                            'description' => Purifier::clean($description),
                            'image' => $meta['image']
                        ]
                    ]
                ];
            }
            else if ($type === 'delimiter')
            {
                $result[] = $row;
            }
            else if ($type === 'list')
            {
                $items = array_filter($row['data']['items'], function ($item)
                {
                    return !!trim($item);
                });
                $result[] = [
                    'type' => $type,
                    'data' => [
                        'style' => $row['data']['style'],
                        'items' => array_map(function ($item)
                        {
                            return Purifier::clean(trim($item));
                        }, $items)
                    ]
                ];
            }
            else if ($type === 'vote')
            {
                $items = array_filter($row['data']['items'], function ($item)
                {
                    return !!trim($item['text']);
                });
                $result[] = [
                    'type' => $type,
                    'data' => [
                        'right_ids' => $row['data']['right_ids'],
                        'max_select' => $row['data']['max_select'],
                        'expired_at' => $row['data']['expired_at'],
                        'items' => array_map(function ($item)
                        {
                            return [
                                'text' => Purifier::clean(trim($item['text'])),
                                'id' => $item['id']
                            ];
                        }, $items)
                    ]
                ];
            }
            else if ($type === 'checklist')
            {
                $items = array_filter($row['data']['items'], function ($item)
                {
                    return !!trim($item['text']);
                });
                $result[] = [
                    'type' => $type,
                    'data' => [
                        'items' => array_map(function ($item)
                        {
                            return [
                                'text' => Purifier::clean(trim($item['text'])),
                                'checked' => $item['checked']
                            ];
                        }, $items)
                    ]
                ];
            }
            else if ($type === 'baidu')
            {
                $url = trim($row['data']['url']);
                if (!preg_match('/https?:\/\/pan\.baidu\.com/', $url))
                {
                    continue;
                }
                $result[] = [
                    'type' => $type,
                    'data' => [
                        'url' => $url,
                        'password' => trim($row['data']['password']),
                        'visit_type' => $row['data']['visit_type']
                    ]
                ];
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function parseRichContent(string $data)
    {
        $data = json_decode($data, true);
        $result = [];
        foreach ($data as $row)
        {
            if ($row['type'] === 'video')
            {
                continue;
            }
            else if ($row['type'] === 'music')
            {
                continue;
            }
            else
            {
                $result[] = $row;
            }
        }
        return $result;
    }

    public function getFirstType($content, $type)
    {
        $array = gettype($content) === 'array' ? json_decode(json_encode($content), true) : json_decode($content, true);
        $result = null;
        foreach ($array as $row)
        {
            if ($row['type'] === $type)
            {
                $result = $row['data'];
                break;
            }
        }

        return $result;
    }

    public function formatVote(array $answers, $right_index, int $max_select = 1, int $expired_at = 0)
    {
        $items = [];
        $ids = [];
        foreach ($answers as $i => $ans)
        {
            $id = $i . str_rand();
            $items[] = [
                'id' => $id,
                'text' => $ans
            ];
            $ids[] = $id;
        }

        $rights = [];
        if (gettype($right_index) !== 'array')
        {
            $rights[] = $ids[$right_index];
        }
        else
        {
            foreach ($right_index as $index)
            {
                $rights[] = $ids[$index];
            }
        }

        if ($max_select < 1)
        {
            $max_select = 1;
        }
        else if ($max_select >= count($items))
        {
            $max_select = count($items) - 1;
        }

        if (strtotime($expired_at) <= time()) {
            $expired_at = 0;
        }

        return [
            'items' => $items,
            'right_ids' => $rights,
            'expired_at' => $expired_at,
            'max_select' => $max_select
        ];
    }

    public function paresPureContent(array $data)
    {
        $result = '';
        foreach ($data as $row)
        {
            $type = $row['type'];
            if ($type === 'paragraph')
            {
                $result .= $row['data']['text'];
            }
            else if ($type === 'header')
            {
                $result .= $row['data']['text'];
            }
            else if ($type === 'list')
            {
                foreach ($row['data']['items'] as $i => $item)
                {
                    $result .= (($i + 1) . ' ' . $item);
                }
            }
            else if ($type === 'checklist')
            {
                foreach ($row['data']['items'] as $i => $item)
                {
                    $result .= (($i + 1) . ' ' . $item['text']);
                }
            }
            else if ($type === 'vote')
            {
                foreach ($row['data']['items'] as $i => $item)
                {
                    $result .= (($i + 1) . ' ' . $item['text']);
                }
            }
            else if ($type === 'image')
            {
                $result .= $row['data']['caption'] ?: '[图片]';
            }
            else if ($type === 'baidu')
            {
                $result .= '[百度资源]';
            }
        }

        return trim($result);
    }

    public function parseRichBanner($data, $banner)
    {
        $images = [];
        foreach ($data as $row)
        {
            if ($row['type'] === 'image')
            {
                $images[] = $row['data']['file'];
            }
        }

        $filterImage = array_filter($images, function ($image)
        {
            return $image['width'] >= 400 && $image['height'] >= 400;
        });

        if (count($filterImage) >= 3)
        {
            return array_slice($filterImage, 0, 3);
        }

        $filterImage = array_filter($images, function ($image)
        {
            return $image['width'] >= 600 && $image['height'] >= 600;
        });

        if (count($filterImage) >= 2)
        {
            return array_slice($filterImage, 0, 2);
        }

        $filterImage = array_filter($images, function ($image)
        {
            return $image['width'] >= 800 && $image['height'] >= 400;
        });

        if (count($filterImage) >= 1)
        {
            return array_slice($filterImage, 0, 1);
        }

        $default = $banner ?: [
            'url' => 'https://m1.calibur.tv/wen.png',
            'mime' => 'image/png',
            'width' => 4000,
            'height' => 2001,
            'size' => 96256
        ];

        return [$default];
    }

    public function detectContentRisk($data, $withImage = true)
    {
        if (gettype($data) === 'string')
        {
            $data = $this->parseRichContent($data);
        }

        $images = [];
        $words = '';

        foreach($data as $row)
        {
            $type = $row['type'];
            if ($type === 'paragraph')
            {
                $words .= $row['data']['text'];
            }
            else if ($type === 'header')
            {
                $words .= $row['data']['text'];
            }
            else if ($type === 'image')
            {
                $words .= $row['data']['caption'];
                $images[] = $row['data']['file']['url'];
            }
            else if ($type === 'title')
            {
                $words .= $row['data']['text'];
                if (isset($row['data']['banner']) && $row['data']['banner'])
                {
                    $images[] = $row['data']['banner']['url'];
                }
            }
            else if ($type === 'link')
            {
                $words .= $row['data']['link'];
                if (isset($row['data']['meta']))
                {
                    $words .= $row['data']['meta']['title'] ?? '';
                    $words .= $row['data']['meta']['description'] ?? '';
                }
            }
            else if ($type === 'list')
            {
                foreach ($row['data']['items'] as $item)
                {
                    $words .= $item;
                }
            }
            else if ($type === 'vote')
            {
                foreach ($row['data']['items'] as $item)
                {
                    $words .= $item['text'];
                }
            }
            else if ($type === 'checklist')
            {
                foreach ($row['data']['items'] as $item)
                {
                    $words .= $item['text'];
                }
            }
        }

        $result = [
          'review' => false,
          'delete' => false,
          'images' => $images,
          'filter' => $words
        ];

        if ($words)
        {
            $wordsFilter = new WordsFilter();
            $count = $wordsFilter->count($words, 2);
            if ($count > 0)
            {
                $result['delete'] = true;
            }

            $count = $wordsFilter->count($words, 1);
            if ($count > 0)
            {
                $result['review'] = true;
            }

            $result['filter'] = $wordsFilter->filter($words);
        }

        if ($withImage && count($images) && config('app.env') !== 'local')
        {
            $imageFilter = new ImageFilter();

            foreach ($images as $url)
            {
                $detect = $imageFilter->check($url);
                if ($detect['delete'])
                {
                    $result['delete'] = true;
                    break;
                }
                if ($detect['review'])
                {
                    $result['review'] = true;
                }
            }
        }

        return $result;
    }
}
