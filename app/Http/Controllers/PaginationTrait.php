<?php
namespace b3nl\RESTScaffolding\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

trait PaginationTrait
{
    /**
     * Returns the class name for rendering the list.
     * @return string
     */
    abstract protected function getListClassName();

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Router $router)
    {
        /** @var Model $listClass */
        $listClass = app($this->getListClassName());

        $baseURI = $router->getCurrentRoute()->getUri();
        $builder = $listClass->newQuery();
        $filter = $request->get('filter', []);
        $limit = (int) $request->get('limit', 30);
        $queryData = $request->query();
        $rows = [];
        $skip = (int) $request->get('skip', 0);
        $sorting = $request->get('sorting', []);

        foreach ($sorting as $field => $direction) {
            $builder->orderBy($field, $direction);
        } // foreach

        $builder->where($filter)->skip($skip)->take($limit)->get();

        $result = $builder->get();
        $return = [];
        $count = count($result);
        $total = $listClass->newQuery()->where($filter)->count();

        if ($total) {
            $links = [
                'self' => ['href' => url($baseURI . ($queryData ? '?' . http_build_query($queryData, null, '&') : ''))]
            ];

            if ($total > $count) {
                if ($skip + $limit < $total) {
                    $links['next'] = ['href' => url(
                        $baseURI . '?' .
                        http_build_query(array_merge($queryData, array('skip' => $skip + $limit)), null, '&')
                    )
                    ];
                }

                if ($skip) {
                    if (!$newSkip = $skip - $limit) {
                        unset($queryData['skip']);
                    } else {
                        $queryData['skip'] = $newSkip;
                    } // else

                    $links['prev'] = [
                        'href' => url($baseURI . ($queryData ? '?' . http_build_query($queryData, null, '') : ''))
                    ];
                } // if
            }

            /** @var Model $row */
            foreach ($result as $index => $row) {
                $rows[$index] = array_merge(
                    ['_links' => ['self' => ['href' => url($baseURI, $row->id)]]],
                    $row->toArray()
                );
            }

            $return = [
                '_embedded' => [
                    $listClass->getTable() => $rows
                ],
                '_links' => $links,
                'count' => $count,
                'take' => $limit,
                'total' => $total,
            ];
        } else if (!$count) {
            abort(404);
        } // elseif

        // TODO 205, links in list.

        return $return;
    }
}
