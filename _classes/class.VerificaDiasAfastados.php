<?php

class VerificaDiasAfastados {
    /*
     * Classe qie informa o afastamento de um servidor específico em um ano
     */

    private $idServidor;
    private $dtInicial;
    private $dtFinal;
    private $ano;
    private $diasAfastados = 0;

    ###########################################################

    /**
     * Método Construtor
     * Aproveita para informar o id do servidor
     */
    public function __construct($idServidor) {
        # Verifica se informou o $idServidor
        if (empty($idServidor)) {
            alert("É necessário informar o idServidor.");
            return;
        } else {
            $this->idServidor = $idServidor;
        }
    }

    ##########################################################

    /*
     * Informa o ano a ser procurado se o servidor tem afastamantos
     * Se não for informado será considerada o ano atual
     */

    function setAno($ano = null) {

        # Quando o ano for null
        if (empty($ano)) {
            $ano = date("Y");
        }

        if (is_numeric($ano)) {
            $this->dtInicial = "{$ano}-01-01";
            $this->dtFinal = "{$ano}-12-31";
            $this->ano = $ano;
        } else {
            alert("É necessário informar o ano.");
            return;
        }
    }

    ###########################################################

    function getDiasAfastados() {
        return $this->diasAfastados;
    }

    ###########################################################

    public function verifica() {

        /*
         * Inicia a variável de retorno
         */
        $retorno = null;

        /*
         * Conecta com o banco
         */
        $pessoal = new Pessoal();

        /*
         *  Férias
         */
        $select = "SELECT dtInicial,
                          ADDDATE(dtInicial,numDias-1) as dtFinal
                     FROM tbferias
                    WHERE idServidor = {$this->idServidor}
                      AND (('{$this->dtFinal}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                       OR ('{$this->dtInicial}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                       OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= ADDDATE(dtInicial,numDias-1)))
                 ORDER BY dtInicial";

        $afast = $pessoal->select($select);

        # Verifica se tem dados
        if (!empty($afast)) {

            # Percorre todos os registros
            foreach ($afast as $item) {

                # Se a data inicial for anterior de 01/01
                if ($item['dtInicial'] < $this->dtInicial) {
                    $item['dtInicial'] = $this->dtInicial;
                }

                # Se a data final for posterior de 31/12
                if ($item['dtFinal'] > $this->dtFinal) {
                    $item['dtFinal'] = $this->dtFinal;
                }

                # Pega a diferença entra as datas
                $date1 = date_create($item['dtInicial']);
                $date2 = date_create($item['dtFinal']);
                $diff = date_diff($date1, $date2);
                $this->diasAfastados += $diff->format("%a") + 1;
            }
        }

        /*
         *  Licenças e Afastamentos gerais
         */
        $select = "SELECT dtInicial,
                          ADDDATE(dtInicial,numDias-1) as dtFinal
                 FROM tblicenca JOIN tbtipolicenca USING (idTpLicenca)
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                   OR ('{$this->dtInicial}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                   OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= ADDDATE(dtInicial,numDias-1))
                   OR (dtInicial <= '{$this->dtFinal}' AND numDias IS NULL))
             ORDER BY dtInicial";

        $afast = $pessoal->select($select);

        # Verifica se tem dados
        if (!empty($afast)) {

            # Percorre todos os registros
            foreach ($afast as $item) {

                # Se a data inicial for anterior de 01/01
                if ($item['dtInicial'] < $this->dtInicial) {
                    $item['dtInicial'] = $this->dtInicial;
                }

                # Se a data final for posterior de 31/12
                if ($item['dtFinal'] > $this->dtFinal OR is_null($item['dtFinal'])) {
                    $item['dtFinal'] = $this->dtFinal;
                }

                # Pega a diferença entra as datas
                $date1 = date_create($item['dtInicial']);
                $date2 = date_create($item['dtFinal']);
                $diff = date_diff($date1, $date2);
                $this->diasAfastados += $diff->format("%a") + 1;
            }
        }

        /*
         *  Licenças prêmio
         */
        $select = "SELECT dtInicial,
                          numDias,
                          ADDDATE(dtInicial,numDias-1) as dtFinal
                 FROM tblicencapremio
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= ADDDATE(dtInicial,numDias-1)))
                         ORDER BY dtInicial";

        $afast = $pessoal->select($select);

        # Verifica se tem dados
        if (!empty($afast)) {

            # Percorre todos os registros
            foreach ($afast as $item) {

                # Se a data inicial for anterior de 01/01
                if ($item['dtInicial'] < $this->dtInicial) {
                    $item['dtInicial'] = $this->dtInicial;
                }

                # Se a data final for posterior de 31/12
                if ($item['dtFinal'] > $this->dtFinal OR is_null($item['dtFinal'])) {
                    $item['dtFinal'] = $this->dtFinal;
                }

                # Pega a diferença entra as datas
                $date1 = date_create($item['dtInicial']);
                $date2 = date_create($item['dtFinal']);
                $diff = date_diff($date1, $date2);
                $this->diasAfastados += $diff->format("%a") + 1;
            }
        }

        /*
         *  Licenças sem vencimentos
         */
        $select = "SELECT dtInicial,
                          numDias,
                          IFNULL(tblicencasemvencimentos.dtretorno, ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1)) as dtFinal
                     FROM tblicencasemvencimentos JOIN tbtipolicenca USING (idTpLicenca)
                    WHERE idServidor = {$this->idServidor}                      
                      AND (('{$this->dtFinal}' BETWEEN dtInicial AND IFNULL(tblicencasemvencimentos.dtretorno, ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1))) 
                       OR ('{$this->dtInicial}' BETWEEN dtInicial AND IFNULL(tblicencasemvencimentos.dtretorno, ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1))) 
                       OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= IFNULL(tblicencasemvencimentos.dtretorno, ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1)))
                       OR (dtInicial <= '{$this->dtFinal}' AND numDias IS NULL))
                 ORDER BY dtInicial";

        $afast = $pessoal->select($select);

        # Verifica se tem dados
        if (!empty($afast)) {

            # Percorre todos os registros
            foreach ($afast as $item) {

                # Se a data inicial for anterior de 01/01
                if ($item['dtInicial'] < $this->dtInicial) {
                    $item['dtInicial'] = $this->dtInicial;
                }

                # Se a data final for posterior de 31/12
                if ($item['dtFinal'] > $this->dtFinal OR is_null($item['dtFinal'])) {
                    $item['dtFinal'] = $this->dtFinal;
                }

                # Pega a diferença entra as datas
                $date1 = date_create($item['dtInicial']);
                $date2 = date_create($item['dtFinal']);
                $diff = date_diff($date1, $date2);
                $this->diasAfastados += $diff->format("%a") + 1;
            }
        }

        /*
         * Licença Médica Sem Alta
         */

        # Variáveis
        $classeLicMedica = new LicencaMedica();
        $dtInicioLicenca = $classeLicMedica->getDtIniciaLicencaAberto($this->idServidor);
                
        # Verifica se tem alguma data inicial cadastrada
        if (!empty($dtInicioLicenca)) {
 
            # Configura as datas
            $data1 = new DateTime($dtInicioLicenca);
            $data2 = new DateTime("{$this->ano}-01-01");
            $data3 = new DateTime("{$this->ano}-12-31");

            # Verifica se data de início da licença em aberto é anterior (maior) a data do início do ano: 01/01
            if ($data1 < $data2) {
                              
                if (anoBissexto($this->ano)) {
                    $this->diasAfastados = 366;
                } else {
                    $this->diasAfastados = 365;
                }
                # verifica se a data é anterior a 31/12
            } elseif ($data1 < $data3) {
               $this->diasAfastados += getNumDias(date_to_php($dtInicioLicenca), "31/12/{$this->ano}");
            }
        }

        ###########################################################
    }

}
