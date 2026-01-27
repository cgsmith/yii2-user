<?php

declare(strict_types=1);

namespace cgsmith\user\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * User search model for admin grid.
 */
class UserSearch extends Model
{
    public ?int $id = null;
    public ?string $email = null;
    public ?string $username = null;
    public ?string $status = null;
    public ?string $created_at = null;
    public ?string $last_login_at = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id'], 'integer'],
            [['email', 'username', 'status', 'created_at', 'last_login_at'], 'safe'],
        ];
    }

    /**
     * Search users.
     */
    public function search(array $params): ActiveDataProvider
    {
        $query = User::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
        ]);

        $query
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'username', $this->username]);

        if (!empty($this->created_at)) {
            $query->andFilterWhere(['DATE(created_at)' => $this->created_at]);
        }

        if (!empty($this->last_login_at)) {
            $query->andFilterWhere(['DATE(last_login_at)' => $this->last_login_at]);
        }

        return $dataProvider;
    }
}
