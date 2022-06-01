<?php

namespace Navb\IdsConnector;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class IdsConnector
{
    public $client;
    public $request;
    public $output_filename;
    public $ids_returned_value;
    private $script_args;

    public function __construct($request)
    {
        $this->request = $request;

        $this->client = new \SoapClient("http://" . config('ids-connector.ids_ip') . "/IDSP.wsdl");

        $this->fixDesignations();

        $this->setOutputFilename();

        $this->setOpenAndSaveFilenames();

        $this->setAllKeys();

        $this->setScriptArgs();
    }

    public function setOutputFilename()
    {
        $this->output_filename = Str::random(10);
    }

    // clean up designations
    public function fixDesignations()
    {
        if ($this->request->designation) {
            $designation_list = array_filter($this->request->designation, function ($value) {
                return $value !== '';
            });
            $designation_list = implode(', ', $designation_list);
            $this->request->merge(['designation' => $designation_list]);
        } else {
            $this->request->merge(['designation' => '']);
        }
    }

    // set open and save filename
    public function setOpenAndSaveFilenames()
    {
        $this->script_args = [
            ["name" => "openName", "value" => $this->request->id_filename],
            ["name" => "saveName", "value" => $this->output_filename],
        ];
    }

    // generates a list of fields to populate on indesign server
    public function setAllKeys()
    {
        $keys = array_keys($this->request->all());

        $full_list = implode(",", $keys);

        $this->script_args = Arr::prepend($this->script_args, ["name" => "full_list", "value" => $full_list]);
    }

    // transform data to key value pair arrays
    public function setScriptArgs()
    {
        foreach ($this->request->all() as $key => $value) {
            $this->script_args = Arr::prepend($this->script_args, ["name" => $key, "value" => trim($value)]);
        }
    }

    public function call()
    {
        $script_data = [
            "runScriptParameters" => [
                "scriptFile" => config('ids-connector.ids_script'),
                "scriptArgs" => $this->script_args,
                "scriptLanguage" => 'javascript'
            ]
        ];

        $return_value = $this->client->RunScript($script_data);

        $this->ids_returned_value = $return_value;

        //For Debugging
        ray($this->script_args, $script_data, $return_value);

        return [
            "preview" => [
                $this->output_filename . '.jpeg',
                $this->output_filename . '2' . '.jpeg'
            ],
            "indd" => $this->output_filename . '.indd',
            "pdf" => $this->output_filename . '.pdf',
        ];
    }
}
