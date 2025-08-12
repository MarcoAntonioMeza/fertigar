<?php
namespace app\models\catalogo;

use app\models\credito\Credito;
use app\models\user\User;
use Yii;

/**
 * This is the model class for table "catalogo_tasa".
 *
 * @property int $id
 * @property int|null $fecha Fecha
 * @property float|null $tiie28
 * @property float|null $tiie91
 * @property float|null $tiie182 Tiie 182
 * @property float|null $term30 Term
 * @property float|null $term90 Term
 * @property float|null $term180 Term
 * @property int|null $status Estatus
 * @property int|null $created_at Creado
 * @property int|null $created_by Creado por
 *
 * @property User $createdBy
 */
class TipoCambio extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE     = 10;
    const STATUS_INACTIVE   = 1;

  
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tipo_cambio';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['fecha'], 'string'],
            [['tipo_cambio'], 'number'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fecha' => 'Fecha',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public static function saveTasa($fecha, $tipoCambio )
    {
        $TipoCambio = TipoCambio::findOne(["fecha" => $fecha]);
        
        if(empty($TipoCambio->id))
            $TipoCambio = new TipoCambio();
        
        $TipoCambio->fecha    = $fecha;
        $TipoCambio->tipo_cambio   = $tipoCambio;
        $TipoCambio->status = self::STATUS_ACTIVE;
        if($TipoCambio->save()){
            return [
                "code" => 202,
                "message" => "Se realizo correctamente "
            ];
        }
        
        return [
            "code" => 10,
            "message" => "Ocurrio un error, intenta nuevamente"
        ];
        
    }
 

    public static function findTasaLoad($fecha)
    {
        return TipoCambio::find()->andWhere(["fecha" => $fecha])->exists();
    }

    public static function getTipoCambio()
    {
        $query = TipoCambio::find()->andWhere(["fecha" => date("Y-m-d")])->one();
        
        return $query ?  $query->tipo_cambio : 0;
    }

   
//------------------------------------------------------------------------------------------------//
// ACTIVE RECORD
//------------------------------------------------------------------------------------------------//
public function beforeSave($insert)
{
    if(parent::beforeSave($insert)) {
        if ($insert) {
            $this->created_at = time();
            $this->created_by = Yii::$app->user->identity? Yii::$app->user->identity->id: null;

        }
        return true;

    } else
        return false;
}
}
