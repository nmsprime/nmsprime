<?php

namespace Modules\ProvVoipEnvia\Entities;

class EnviaOrderDocument extends \BaseModel
{
    // The associated SQL table for this Model
    public $table = 'enviaorderdocument';

    // where to save the uploaded documents (relative to /storage/app)
    public static $document_base_path = 'data/provvoipenvia/EnviaOrderDocuments';

    public static $allowed_mimetypes = [
        'application/msword',
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/tif',
    ];

    // API allows max. 3MB; give as string in kilobyte
    public static $allowed_max_upload_filesize = 3072;

    // Add your validation rules here
    public static function rules($id = null)
    {

        // for validation rule we only need the concrete type (e.g. pdf instead of application/pdf)
        $mimes_short = [];
        foreach (self::$allowed_mimetypes as $mime) {
            array_push($mimes_short, explode('/', $mime)[1]);
        }
        $mimestring = implode(',', $mimes_short);

        return [
            'document_type' => 'required',
            'document_upload' => 'required|mimes:'.$mimestring.'|max:'.self::$allowed_max_upload_filesize,
            'enviaorder_id' => 'required|exists:enviaorder,id,deleted_at,NULL',
            'mime_type' => 'required',
        ];
    }

    // Don't forget to fill this array
    protected $fillable = [
        'document_type',
        'mime_type',
        'filename',
        'uploaded_order_id',
        'enviaorder_id',
    ];

    // Name of View
    public static function view_headline()
    {
        return 'EnviaOrderDocuments';
    }

    // link title in index view
    public function view_index_label()
    {
        $bsclass = 'success';

        return ['index' => [$this->id],
                'index_header' => ['ID'],
                'bsclass' => $bsclass,
                'header' => $this->created_at.': '.$this->document_type.' ('.$this->upload_order_id.')', ];
    }

    // belongs to a modem - see BaseModel for explanation
    public function view_belongs_to()
    {
        return $this->enviaorder;
    }

    public function enviaorder()
    {
        return $this->belongsTo('Modules\ProvVoipEnvia\Entities\EnviaOrder', 'enviaorder_id');
    }
}
