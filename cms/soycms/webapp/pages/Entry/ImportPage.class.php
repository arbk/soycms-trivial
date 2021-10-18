<?php

class ImportPage extends CMSWebPageBase
{
    private $dao;

    public function __construct()
    {
        parent::__construct();
    }

    public function main()
    {
        $this->addForm("import_form", array(
             "ENCTYPE" => "multipart/form-data"
        ));
    }

    public function doPost()
    {
        //check token
        if (!soy2_check_token()) {
            $this->jump("Entry.Import?fail");
            exit;
        }

        set_time_limit(EXEC_TIME_NO_LIMIT);

        $file  = $_FILES["import_file"];

        $logic = SOY2Logic::createInstance("logic.site.Entry.ExImportLogic");
        $format = $_POST["format"];
        $item = $_POST["item"];

//      $displayLabel = (isset($format["label"])) ? $format["label"] : null;
        if (isset($format["separator"])) {
            $logic->setSeparator($format["separator"]);
        }
        if (isset($format["quote"])) {
            $logic->setQuote($format["quote"]);
        }
        if (isset($format["charset"])) {
            $logic->setCharset($format["charset"]);
        }
        $logic->setItems($item);

//      $logic->setCustomFields($this->getCustomFieldList(true));

        if (!$logic->checkUploadedFile($file)) {
            $this->jump("Entry.Import?fail");
            exit;
        }

//      if(!$logic->checkFileContent($file)){
//          $this->jump("Entry.Import?invalid");
//          exit;
//      }

        //ファイル読み込み・削除
        $fileContent = file_get_contents($file["tmp_name"]);
        unlink($file["tmp_name"]);

        //データを行単位にばらす
        $lines = $logic->GET_CSV_LINES($fileContent);  //fix multiple lines

        //先頭行削除
        if (isset($format["label"])) {
            array_shift($lines);
        }

        //DAO
        $this->dao = SOY2DAOFactory::create("cms.EntryDAO");

        $this->dao->begin();

        //データ更新
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            list($obj) = $logic->import($line);
            $deleted = ($obj["id"] == "delete");

            $entry = self::import($obj);

            if (strlen($entry->getAlias()) > 0) {
                if ($deleted) {
                    self::deleteItem($entry);
                } else {
                    $id = self::insertOrUpdate($entry);
                    //idの使いみちは今のところない
                }
            }
        }

        $this->dao->commit();

        $this->jump("Entry.Import?updated");
    }

    /**
     * CSV, TSVの一行からEntryを作り、返す
     *
     * idでチェックを行う
     *
     * @param String $line
     * @param Array $properties
     * @return Entry
     */
    private function import($obj)
    {
        if (isset($obj["id"])) {
            unset($obj["id"]);
        }
        $entry = SOY2::cast("Entry", (object)$obj);

        try {
            $entry = $this->dao->getByAlias($entry->getAlias());
            SOY2::cast($entry, (object)$obj);
        } catch (Exception $e) {
            //
        }

        //エイリアスがない場合はタイトルをエイリアスを入れる
        if (!strlen($entry->getAlias())) {
            $entry->setAlias($entry->getTitle());
        }

        return $entry;
    }

    /**
     * 商品データの更新または挿入を実行する
     * 同じメールアドレスのユーザがすでに登録されている場合に更新を行う
     * @param Entry
     * @return id
     */
    public function insertOrUpdate(Entry $entry)
    {
        if (strlen($entry->getId())) {
            self::update($entry);
            return $entry->getId();
        } else {
            return self::insert($entry);
        }
    }

    /**
     * 商品データの挿入を実行する
     * @param Entry
     */
    private function insert(Entry $entry)
    {
        try {
            return $this->dao->insert($entry);
        } catch (Exception $e) {
            var_dump($e);
            return null;
        }
    }

    /**
     * 商品データの更新を実行する
     * @param Entry
     */
    private function update(Entry $entry)
    {
        try {
            $this->dao->update($entry);
        } catch (Exception $e) {
      //
        }
    }

    private function deleteItem(Entry $entry)
    {
        try {
            $this->dao->deleteByAlias($entry>getAlias());
        } catch (Exception $e) {
            //
        }
    }

//  private function getCustomFieldList($flag = false){
//      $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
//      $config = SOYShop_ItemAttributeConfig::load($flag);
//      return $config;
//  }
}
