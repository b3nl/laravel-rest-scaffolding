<?php
namespace {appNamespace}\Http\Requests\{customNamespace}\{entityClass};

use {appNamespace}\Http\Requests\Request;

/**
 * Request class updateing the entitiy.
 * @category Requests
 * @package {appNamespace}
 * @subpackage Http
 * @version $id$
 */
class UpdateRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     * @return bool
     */
    public function authorize()
    {
        // TODO Check for user!
        return true;
    } // function

    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules()
    {
        $entity = $this->route('{tableName}');

        return {validationRules};
    } // function
}
