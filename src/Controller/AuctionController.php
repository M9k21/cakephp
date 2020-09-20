<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Exception;

class AuctionController extends AuctionBaseController
{
    // デフォルトテーブルを使わない
    public $useTable = false;

    // 初期化処理
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Paginator');
        // 必要なモデルをすべてロード
        $this->loadModel('Users');
        $this->loadModel('Biditems');
        $this->loadModel('Bidrequests');
        $this->loadModel('Bidinfo');
        $this->loadModel('Bidmessages');
        // ログインしているユーザー情報をauthuserに設定
        $this->set('authuser', $this->Auth->user());
        // レイアウトをauctionに変更
        $this->viewBuilder()->setLayout('auction');
    }

    // トップページ
    public function index()
    {
        // ページネーションでBiditemsを取得
        $auction = $this->paginate('Biditems', [
            'order' =>  ['endtime' => 'desc'],
            'limit' => 10
        ]);
        $this->set(compact('auction'));
    }

    // 商品情報の表示
    public function view($id = null)
    {
        // $idのBiditemを取得
        $biditem = $this->Biditems->get($id, [
            'contain' => ['Users', 'Bidinfo', 'Bidinfo.Users']
        ]);
        // オークション終了時の処理
        if ($biditem->endtime < new \DateTime('now') and $biditem->finished == 0) {
            // finishedを1に変更して保存
            $biditem->finished = 1;
            $this->Biditems->save($biditem);
            // Bidinfoを作成する
            $bidinfo = $this->Bidinfo->newEntity();
            // Bidinfoのbiditem_idに$idを設定
            $bidinfo->biditem_id = $id;
            // 最高金額のBidrequestを検索
            $bidrequest = $this->Bidrequests->find('all', [
                'conditions' => ['biditem_id' => $id],
                'contain' => ['Users'],
                'order' => ['price' => 'desc']
            ])->first();
            // Bidrequestが得られた時の処理
            if (!empty($bidrequest)) {
                // Bidinfoの各種プロパティを設定して保存する
                $bidinfo->user_id = $bidrequest->user->id;
                $bidinfo->user = $bidrequest->user;
                $bidinfo->price = $bidrequest->price;
                $this->Bidinfo->save($bidinfo);
            }
            // Biditemのbidinfoに$bidinfoを設定
            $biditem->bidinfo = $bidinfo;
        }
        // Bidrequestsからbiditem_idが$idのものを取得
        $bidrequests = $this->Bidrequests->find('all', [
            'conditions' => ['biditem_id' => $id],
            'contain' => ['Users'],
            'order' => ['price' => 'desc']
        ])->toArray();
        // JavaScriptに渡す値をjsonに変換
        $json = json_encode(
            [
                'endtime' => $biditem->endtime,
                'nowtime' => time()
            ],
            JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS
        );
        // オブジェクト類をテンプレート用に設定
        $this->set(compact('biditem', 'bidrequests', 'json'));
    }

    // 出品する処理
    public function add()
    {
        $session = $this->getRequest()->getSession();
        // 修正の場合の処理
        if (!empty($this->request->getQuery('additem'))) {
            if ($session->check('add_biditem')) {
                $biditem = $session->consume('add_biditem');
                $this->Flash->error(__('恐れ入りますが、画像を改めて指定してください。'));
            } else {
                return $this->redirect(['action' => 'add']);
            }
        } elseif ($session->check('before_validate')) {
            // 入力エラーによる再表示の場合
            $biditem = $session->consume('before_validate');
        } else {
            // Biditemインスタンスを用意
            $biditem = $this->Biditems->newEntity();
        }
        // POST送信時の処理
        if ($this->request->is('post')) {
            // フォームの送信内容を反映
            $request_data = $this->request->getData();
            // ファイル名を格納
            $request_file = $this->request->getData('image');
            $request_data['image'] = date('YmdHis') . $request_file['name'];
            $session->write('before_validate', $biditem);
            // 画像のバリデーション処理
            $this->fileValidation($request_data, $request_file);
            // $biditemに値を保管
            $biditem->user_id = $request_data['user_id'];
            $biditem->name =  $request_data['name'];
            $biditem->description = $request_data['description'];
            $biditem->finished = $request_data['finished'];
            $biditem->endtime = $request_data['endtime'];
            $biditem->image = $request_data['image'];
            // セッションに値を保管
            $session->write('add_biditem', $biditem);
            return $this->redirect(['action' => 'confirm']);
        }
        // 値を保管
        $this->set(compact('biditem'));
    }

    private function fileValidation($request_data, $request_file)
    {
        // ファイル形式のチェック
        $allowFileType = array('image/jpeg', 'image/png', 'image/gif');
        if (!in_array($request_file['type'], $allowFileType)) {
            $this->Flash->error(__('PNG、JPGまたはGIFの画像ファイルのみアップロードできます。'));
            return $this->redirect(['action' => 'add']);
        }
        // ファイル容量のチェック
        if ($request_file['size'] > 5242880) {
            $this->Flash->error(__('5MB以下の画像ファイルを指定してください。'));
            return $this->redirect(['action' => 'add']);
        }
        // 画像ファイルの保存
        $filePath = WWW_ROOT . DS . 'img' . DS . 'uploaded' . DS . $request_data['image'];
        // 画像ファイルをディレクトリに保存
        move_uploaded_file($request_file['tmp_name'], $filePath);
        $this->getRequest()->getSession()->delete('before_validate');
    }

    // 出品情報の確認
    public function confirm()
    {
        $session = $this->getRequest()->getSession();
        $connection = ConnectionManager::get('default');
        if ($session->check('add_biditem')) {
            // セッションに値が入っているか確認する
            $biditem = $session->read('add_biditem');
        } else {
            return $this->redirect(['action' => 'add']);
        }
        // POST送信時の処理
        if ($this->request->is('post')) {
            $connection->begin();
            try {
                // $biditemの値を保管
                $biditem = $this->Biditems->patchEntity($biditem, $this->request->getData());
                if ($biditem->getErrors()) {
                    $this->Flash->error(__('入力内容を確認してください。'));
                    return $this->redirect(['action' => 'add', '?' => ['additem' => 'rewrite']]);
                }
                // $biditemを保存する
                if ($this->Biditems->save($biditem)) {
                    // セッションの削除
                    $session->delete('add_biditem');
                    // 成功時のメッセージ
                    $this->Flash->success(__('保存しました。'));
                    $connection->commit();
                    // トップページ(index)に移動
                    return $this->redirect(['action' => 'index']);
                }
            } catch (Exception $e) {
                // 失敗時のメッセージ
                $this->Flash->error(__('保存に失敗しました。もう一度入力下さい。'));
                $connection->rollback();
                return $this->redirect(['action' => 'add', '?' => ['additem' => 'rewrite']]);
            }
        }
        // 値を保管
        $this->set(compact('biditem'));
    }

    // 入札の処理
    public function bid($biditem_id = null)
    {
        // $biditem_idの$biditemを取得する
        $biditem = $this->Biditems->get($biditem_id);
        if ($biditem->endtime < new \DateTime('now')) {
            $this->Flash->error(__('入札は終了しました。'));
            return $this->redirect(['action' => 'view', $biditem_id]);
        }
        // 入札用のBidrequestインスタンスを用意
        $bidrequest =  $this->Bidrequests->newEntity();
        // $bidrequestにbiditem_idとuser_idを設定
        $bidrequest->biditem_id = $biditem_id;
        $bidrequest->user_id =  $this->Auth->user('id');
        // POST送信時の処理
        if ($this->request->is('post')) {
            // $bidrequestに送信フォームの内容を反映する
            $bidrequest = $this->Bidrequests->patchEntity($bidrequest, $this->request->getData());
            // Bidrequestを保存
            if ($this->Bidrequests->save($bidrequest)) {
                // 成功時のメッセージ
                $this->Flash->success(__('入札を送信しました。'));
                // トップページにリダイレクト
                return $this->redirect(['action' => 'view', $biditem_id]);
            }
            // 失敗時のメッセージ
            $this->Flash->error(__('入札に失敗しました。もう一度入力下さい。'));
        }
        $this->set(compact('bidrequest', 'biditem'));
    }

    // 落札者とのメッセージ
    public function msg($bidinfo_id = null)
    {
        // Bidmessageを新たに用意
        $bidmsg = $this->Bidmessages->newEntity();
        // POST送信時の処理
        if ($this->request->is('post')) {
            // 送信されたフォームで$bidmsgを更新
            $bidmsg = $this->Bidmessages->patchEntity($bidmsg, $this->request->getData());
            // Bidmessageを保存
            if ($this->Bidmessages->save($bidmsg)) {
                $this->Flash->success(__('保存しました。'));
            } else {
                $this->Flash->error(__('保存に失敗しました。もう一度入力下さい。'));
            }
        }
        try {
            // $bidinfo_idからBidinfoを取得する
            $bidinfo = $this->Bidinfo->get($bidinfo_id, ['contain' => ['Biditems']]);
        } catch (Exception $e) {
            $bidinfo = null;
        }
        // Bidmessageをbidinfo_idとuser_idで検索
        $bidmsgs = $this->Bidmessages->find('all', [
            'conditions' => ['bidinfo_id' => $bidinfo_id],
            'contain' => ['Users'],
            'order' => ['created' => 'desc']
        ]);
        $this->set(compact('bidmsgs', 'bidinfo', 'bidmsg'));
    }

    // 落札情報の表示
    public function home()
    {
        // 自分が落札したBidinfoをページネーションで取得
        $bidinfo = $this->paginate('Bidinfo', [
            'conditions' => ['Bidinfo.user_id' => $this->Auth->user('id')],
            'contain' => ['Users', 'Biditems'],
            'order' => ['created' => 'desc'],
            'limit' => 10
        ])->toArray();
        $this->set(compact('bidinfo'));
    }

    // 出品情報の表示
    public function home2()
    {
        // 自分が出品したBiditemをページネーションで取得
        $biditems = $this->paginate('Biditems', [
            'conditions' => ['Biditems.user_id' => $this->Auth->user('id')],
            'contain' => ['Users', 'Bidinfo'],
            'order' => ['created' => 'desc'],
            'limit' => 10
        ])->toArray();
        $this->set(compact('biditems'));
    }
}
