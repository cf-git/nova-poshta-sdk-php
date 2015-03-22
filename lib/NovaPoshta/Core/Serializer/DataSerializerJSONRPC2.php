<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 13.03.2015
 * Time: 22:02
 */

namespace NovaPoshta\Core\Serializer;

use NovaPoshta\Models\DataContainer;
use NovaPoshta\Models\DataContainerResponse;

class DataSerializerJSONRPC2 extends SerializerFactory implements SerializerInterface, SerializerBatchInterface
{
    public function serializeData(DataContainer $data)
    {
        $dataJSONRPC2 = $this->dataContainer2dataSerializerJSONRPC2(array($data));

        $json = json_encode($dataJSONRPC2[0]);

        return $json;
    }

    public function unserializeData($json)
    {
        $data = (array)json_decode($json);
        $dataContainer = $this->dataSerializerJSONRPC2dataContainer(array($data));

        return $dataContainer[0];
    }

    public function serializeBatchData(array $data)
    {
        $dataJSONRPC2 = $this->dataContainer2dataSerializerJSONRPC2($data);

        $json = json_encode($dataJSONRPC2);

        return $json;
    }

    public function unserializeBatchData($json)
    {
        $data = (array)json_decode($json);
        $dataContainer = $this->dataSerializerJSONRPC2dataContainer($data);

        return $dataContainer;
    }

    private function dataContainer2dataSerializerJSONRPC2(array $dataItems)
    {
        $data = array();

        foreach($dataItems as $item){
            $dataJSONRPC2 = new \stdClass();
            $dataJSONRPC2->id = $item->id;
            $dataJSONRPC2->method = $item->getModelName() . '.' . $item->getCalledMethod();
            $dataJSONRPC2->params = new \stdClass();
            $dataJSONRPC2->params->methodProperties = $item->getMethodProperties();
            $dataJSONRPC2->params->language = $item->getLanguage();
            $dataJSONRPC2->params->apiKey = $item->getApiKey();

            $data[] = $dataJSONRPC2;
        }

        return $data;
    }

    private function dataSerializerJSONRPC2dataContainer($dataItems)
    {
        $dataContainers = array();
        foreach($dataItems as $data){
            if (json_last_error() != JSON_ERROR_NONE) {
                $dataContainerResponse = new DataContainerResponse();
                $dataContainerResponse->success = false;
                $dataContainerResponse->errors[] = array('DataSerializerJSONRPC2.DATA_IS_INVALID');

                return $dataContainerResponse;
            }
            $data = json_decode(json_encode($data), false);

            $dataContainer = new DataContainerResponse();
            $dataContainer->success = isset($data->result) ? true : false;
            if ($dataContainer->success) {
                $dataContainer->data = $data->result;
            } else {
                if (isset($data->error)) {
                    $dataContainer->errors = isset($data->error->data) ? $data->error->data : '';
                    $dataContainer->errors['code'] = isset($data->error->code) ? $data->error->code : '';
                    $dataContainer->errors['message'] = isset($data->error->message) ? $data->error->message : '';
                } else {
                    $dataContainer->errors[] = 'DataSerializerJSONRPC2.DATA_ERRORS_IS_EMPTY';
                }
            }
            $dataContainer->warnings = $data->warnings;
            $dataContainer->info = $data->info;
            $dataContainer->id = $data->id;

            $dataContainers[] = $dataContainer;
        }

        return $dataContainers;
    }
}