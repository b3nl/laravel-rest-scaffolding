<?php
namespace b3nl\RESTScaffolding\Http\Controllers;

use Illuminate\Contracts\Pagination\Paginator;
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
        $builder = $listClass->newQuery();

        foreach ($request->get('sorting', []) as $field => $direction) {
            $builder->orderBy($field, $direction);
        } // foreach

        /** @var Paginator $pagination */
        $pagination = $builder
            ->where($request->get('filter', []))
            ->paginate((int)$request->get('limit', 30))
            ->appends($request->query());

        $count = count($pagination);
        $return = [];

        if ($total = $pagination->total()) {
            $baseURI = $router->getCurrentRoute()->getUri();
            $rows = [];
            $links = ['self' => ['href' => $pagination->url($pagination->currentPage())]];

            if ($total > $count) {
                $links['first'] = ['href' => $pagination->url(1)];
                $links['last'] = ['href' => $pagination->url($pagination->lastPage())];

                if ($pagination->hasMorePages()) {
                    $links['next'] = ['href' => $pagination->nextPageUrl()];
                }

                if ($pagination->currentPage() > 1) {
                    $links['prev'] = ['href' => $pagination->previousPageUrl()];
                } // if
            }

            /** @var Model $row */
            foreach ($pagination as $index => $row) {
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
                'take' => $pagination->perPage(),
                'total' => $total,
            ];
        } else {
            if (!$count) {
                abort(404);
            }
        } // elseif

        // TODO 205, links in list.

        return $return;
    }
}
