<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController {
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Show the subscription page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSubscriptions(Request $request) {
        if (!config('procyon-settings.web-interface')) {
            abort(404);
        }

        if (config('procyon-settings.web-password')) {
            if ($request->only(['password']) && $request->get('password') == config('procyon-settings.web-password')) {
                session()->put('allowAccess', true);

                return redirect('/subscriptions');
            }

            if (session()->get('allowAccess')) {
                $allowAccess = true;
            } else {
                $allowAccess = false;
            }
        } else {
            $allowAccess = true;
        }

        return view('subscriptions', [
            'allowAccess'   => $allowAccess ?? false,
            'subscriptions' => Subscription::with('digests')->get(),
        ]);
    }

    /**
     * Updates subscriptions.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSubscriptions(Request $request, Subscription $model) {
        if ($model->updateSubscriptions($request->get('url'))) {
            flash('Subscriptions updated successfully.')->success();
        } else {
            flash('Failed to update subscriptions.')->error();
        }

        return redirect()->back();
    }
}
