<?php
namespace {appNamespace}\Http\Controllers\{customNamespace};

use {appNamespace}\Http\Requests\{customNamespace}\{entityClass}\StoreRequest;
use {appNamespace}\Http\Requests\{customNamespace}\{entityClass}\UpdateRequest;
use {appNamespace}\Http\Requests;
use {appNamespace}\Http\Controllers\Controller;
use b3nl\RESTScaffolding\Http\Controllers\PaginationTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
{customUsages}

/**
 * The basic controller for table api requests.
 * @category Controllers
 * @package {appNamespace}
 * @subpackage Http
 * @version $id$
 */
class {tableNamespace}Controller extends Controller
{
    use PaginationTrait;

    /**
     * Deletes the given row.
     * @param {entityClass} $entity
     * @return {entityClass}
     */
    public function destroy({entityClass} $entity)
    {
        $entity->delete();

        return $entity;
    } // function

    /**
     * Returns the class name for rendering the list.
     * @return string
     */
    protected function getListClassName() {
        return {entityClass}::class;
    } // function

    /**
     * Returns the entity.
     * @param {entityClass} $entity
     * @return {entityClass}
     */
    public function show({entityClass} $entity)
    {
        return $entity;
    } // function

    /**
     * Saves the entity.
     * @param StoreRequest $request
     * @return {entityClass}
     */
    public function store(StoreRequest $request)
    {
        return {entityClass}::create($request->all());
    } // function

    /**
     * Updates the entity.
     * @param UpdateRequest $request
     * @param {entityClass} $entity
     * @return {entityClass}
     */
    public function update(UpdateRequest $request, {entityClass} $entity)
    {
        $entity->update($request->all());

        return $entity;
    } // function
}
