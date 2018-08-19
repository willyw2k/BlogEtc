<?php

namespace WebDevEtc\BlogEtc\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use WebDevEtc\BlogEtc\BaseRequestInterface;
use WebDevEtc\BlogEtc\Helpers;

/**
 * Class BaseRequest
 * @package WebDevEtc\BlogEtc\Requests
 */
abstract class BaseRequest extends FormRequest implements BaseRequestInterface
{


    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \Auth::check() && \Auth::user()->canManageBlogEtcPosts();
    }

    /**
     * Shared rules for categories
     * @return array
     */
    protected function baseCategoryRules()
    {

        $return = [
            //
            'category_name' => ['required', 'string', 'min:1', 'max:100'],
            'slug' => ['required', 'alpha_dash']
        ];
        return $return;
    }

    /**
     * Shared rules for blog posts
     *
     * @return array
     */
    protected function BaseBlogPostRules()
    {
        $return = [
            //
            'posted_at' => ['nullable',

                function ($attribute, $value, $fail) {
                    // just the 'date' validation can cause errors ("2018-01-01 a" passes the validation, but causes a carbon error)
                    try {
                        Carbon::createFromFormat('Y-m-d H:i:s', $value);
                    } catch (\Exception $e) {
                        return $fail('Posted at is not a valid date');
                    }
                }

            ],
            'title' => ['required', 'string', 'min:1', 'max:255'],
            'subtitle' => ['nullable', 'string', 'min:1', 'max:255'],

            'post_body' => ['required_without:use_view_file', 'max:20000'],

            'meta_desc' => ['nullable', 'string', 'min:1', 'max:1000'],

            'slug' => [
                'nullable', 'string', 'min:1', 'max:150', 'alpha_dash', // this field should have some extra, which is done in the subclasses.
            ],

            'categories' => ['nullable', 'array'],

            'use_view_file' => ['nullable', 'string', 'alpha_num', 'min:1', 'max:75',],
        ];

        foreach ((array) config('blogetc.image_sizes') as $size => $image_detail) {

            if ($image_detail['enabled']) {
                $return[$size] = [
                    'nullable',
                    'image',
                ];
            } else {

                $return[$size] = function ($attribute, $value, $fail) {
                    if ($value) {
                        return $fail($attribute . ' must be empty');
                    }
                };


            }

        }

        return $return;
    }


}