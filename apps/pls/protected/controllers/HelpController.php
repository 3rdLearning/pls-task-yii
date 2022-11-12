<?php

/**
 * @class      HelpController
 *
 * This is the controller that contains the /help actions.
 *
 * @author     Developer
 * @copyright  PLS 3rd Learning, Inc. All rights reserved.
 */
class HelpController extends Controller {

	/**
	 * Specifies the action filters.
	 *
	 * @return array action filters
	 */
	public function filters() {
		return [
			'accessControl', // perform access control for CRUD operations
		];
	}

	/**
	 * Specifies the access control rules.
	 *
	 * @return array access control rules
	 */
	public function accessRules() {
		return [
			['allow',  // allow all users to access specified actions.
				'actions' => ['index', 'contact', 'updates'],
				'users'   => ['*'],
			],
			['allow', // allow authenticated users to access all actions
				'users' => ['@'],
			],
			['deny',  // deny all users
				'users' => ['*'],
			],
		];
	}

	/**
	 * Declares class-based actions.
	 *
	 * @return array[]
	 */
	public function actions() {
		return [
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha' => [
				'class'     => 'CCaptchaAction',
				'backColor' => 0xFFFFFF,
			],
		];
	}

	/**
	 * Renders the contact page and handles form submission.
	 *
	 * @return void
	 */
	public function actionContact() {
		$model = new ContactForm();
		if (!empty(Yii::app()->request->getPost('ContactForm'))) {
			$model->attributes = Yii::app()->request->getPost('ContactForm');
			if ($model->validate()) {
				$headers = [
					'From'     => $model->email,
					'Reply-To' => $model->email,
				];
				mail(Yii::app()->params['adminEmail'], $model->subject, $model->body, $headers);
				Yii::app()->user->setFlash('contact', 'Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact', ['model' => $model]);
	}

	/**
	 * Renders the help videos page.
	 *
	 * @return void
	 */
	public function actionVideos() {
		// Not implemented
	}

	/**
	 * Renders the user manuals page.
	 *
	 * @return void
	 */
	public function actionManuals() {
		// Not implemented
	}

	/**
	 * Renders the latest updates page based on an RSS feed.
	 *
	 * @return void
	 * @throws FeedException
	 */
	public function actionUpdates() {
		Feed::$userAgent = Yii::app()->params['curlUserAgent'];
		Feed::$cacheDir = Yii::app()->params['latestUpdatesFeedCacheDir'];
		Feed::$cacheExpire = Yii::app()->params['latestUpdatesFeedCacheExp'];
		$feed = Feed::loadRss(Yii::app()->params['latestUpdatesFeedUrl']);

        $feedCount = 0;
        $rssData = [];
		if (!empty($feed)) {
			foreach ($feed->item as $item) {
                $aux = new stdClass();
                if ($feedCount >= 5) {
                    continue;
                }
				$more = ' <a href="' . $item->link . '" target="_blank">Read more</a>';
                $aux->description = trim(str_replace(' [&#8230;]', '...' . $more, $item->description));
                $aux->description = preg_replace('/The post.*appeared first on .*\./', '', $aux->description);
                $aux->link = $item->link;
                $aux->title = $item->title;

                $rssData[] = $aux;
                $feedCount++;
			}
		}
		$this->render('updates', ['updates' => $rssData]);
	}

}