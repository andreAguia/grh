<?php

class VerificaAfastamentos {
    /*
     * Classe qie informa o afastamento de um servidor específico
     */

    private $idServidor;

    /*
     * do período
     */
    private $dtInicial;
    private $dtFinal;

    /*
     * da isenção
     */
    private $tabela;
    private $id;

    /*
     * do retorno
     */
    private $afastamento = false;
    private $detalhe = null;

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
     * Informa o período a ser procurado se o servidor tem afastamantos
     * Se não for informado será considerada a data de hoje
     */

    function setPeriodo($dtInicial, $dtFinal) {
        if (empty($dtInicial)) {
            alert("É necessário informar a data inicial.");
            return;
        } else {
            $this->dtInicial = date_to_bd($dtInicial);
        }

        if (empty($dtFinal)) {
            alert("É necessário informar o data Finalr.");
            return;
        } else {
            $this->dtFinal = date_to_bd($dtFinal);
        }
    }

    ###########################################################
    /*
     * Se tem que retiirar ou isentar algum registro e tabela da busca
     * Usado em rotinas de cadastro de afastamento para não achar a si mesma
     */

    public function setIsento($tabela, $id) {
        $this->tabela = $tabela;
        $this->id = $id;
    }

    ###########################################################
    /*
     * Pega os valores de retorno
     */

    function getAfastamento() {
        return $this->afastamento;
    }

    function getDetalhe() {
        return $this->detalhe;
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

        # Altera para hoje quando o período está em branco
        if (empty($this->dtInicial)) {
            $this->dtInicial = date("Y-m-d");
            $this->dtFinal = date("Y-m-d");
        }

        /*
         *  Férias
         */
        $select = "SELECT idFerias, anoExercicio
                 FROM tbferias
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= ADDDATE(dtInicial,numDias-1)))";

        // se tiver isenção
        if ($this->tabela == "tbferias" AND !empty($this->id)) {
            $select .= " AND idFerias <> {$this->id}";
        }

        $select .= " ORDER BY dtInicial";

        $afast = $pessoal->select($select, false);

        if (!empty($afast)) {
            $this->afastamento = "Férias";
            $this->detalhe = "Exercício {$afast['anoExercicio']}";
            return true;
        }

        /*
         *  Licenças e Afastamentos gerais
         */
        $select = "SELECT idLicenca, tbtipolicenca.nome
                 FROM tblicenca JOIN tbtipolicenca USING (idTpLicenca)
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= ADDDATE(dtInicial,numDias-1)))";

        // se tiver isenção
        if ($this->tabela == "tblicenca" AND !empty($this->id)) {
            $select .= " AND idLicenca <> {$this->id}";
        }

        $select .= " ORDER BY dtInicial";

        $afast = $pessoal->select($select, false);

        if (!empty($afast)) {
            $this->afastamento = "Licença";
            $this->detalhe = $afast['nome'];
            return true;
        }

        /*
         *  Licenças prêmio
         */
        $select = "SELECT idLicencaPremio
                 FROM tblicencapremio
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= ADDDATE(dtInicial,numDias-1)))";

        // se tiver isenção
        if ($this->tabela == "tblicencapremio" AND !empty($this->id)) {
            $select .= " AND idLicencaPremio <> {$this->id}";
        }

        $select .= " ORDER BY dtInicial";

        $afast = $pessoal->select($select, false);

        if (!empty($afast)) {
            $this->afastamento = "Licença";
            $this->detalhe = "Licença Prêmio";
            return true;
        }

        /*
         *  Licenças sem vencimentos
         */
        $select = "SELECT idLicencaSemVencimentos, tbtipolicenca.nome
                 FROM tblicencasemvencimentos JOIN tbtipolicenca USING (idTpLicenca)
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1)) 
                     OR ('{$this->dtInicial}' <= dtInicial AND '{$this->dtFinal}' >= ADDDATE(dtInicial,numDias-1)))";

        // se tiver isenção
        if ($this->tabela == "tblicencasemvencimentos" AND !empty($this->id)) {
            $select .= " AND idLicencaSemVencimentos <> {$this->id}";
        }

        $select .= " ORDER BY dtInicial";

        $afast = $pessoal->select($select, false);

        if (!empty($afast)) {
            $this->afastamento = "Licença";
            $this->detalhe = $afast['nome'];
            return true;
        }

        /*
         *  Faltas Abonadas por atestado
         */
        $select = "SELECT idAtestado
                 FROM tbatestado
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN dtInicio AND ADDDATE(dtInicio,numDias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN dtInicio AND ADDDATE(dtInicio,numDias-1)) 
                     OR ('{$this->dtInicial}' <= dtInicio AND '{$this->dtFinal}' >= ADDDATE(dtInicio,numDias-1)))";

        // se tiver isenção
        if ($this->tabela == "tbatestado" AND !empty($this->id)) {
            $select .= " AND idAtestado <> {$this->id}";
        }

        $select .= " ORDER BY dtInicio";

        $afast = $pessoal->select($select, false);

        if (!empty($afast)) {
            $this->afastamento = "Falta Abonada";
            $this->detalhe = "Atestado Médico";
            return true;
        }

        /*
         *  Trabalho TRE
         */
        $select = "SELECT idTrabalhoTre
                 FROM tbtrabalhotre
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN data AND ADDDATE(data,dias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN data AND ADDDATE(data,dias-1)) 
                     OR ('{$this->dtInicial}' <= data AND '{$this->dtFinal}' >= ADDDATE(data,dias-1)))";

        // se tiver isenção
        if ($this->tabela == "tbtrabalhotre" AND !empty($this->id)) {
            $select .= " AND idTrabalhoTre <> {$this->id}";
        }

        $select .= " ORDER BY data";

        $afast = $pessoal->select($select, false);

        if (!empty($afast)) {
            $this->afastamento = "TRE";
            $this->detalhe = "Trabalhando no TRE";
            return true;
        }

        /*
         *  Folgas TRE
         */
        $select = "SELECT idFolga
                 FROM tbfolga
                WHERE idServidor = {$this->idServidor}
                  AND (('{$this->dtFinal}' BETWEEN data AND ADDDATE(data,dias-1)) 
                     OR ('{$this->dtInicial}' BETWEEN data AND ADDDATE(data,dias-1)) 
                     OR ('{$this->dtInicial}' <= data AND '{$this->dtFinal}' >= ADDDATE(data,dias-1)))";

        // se tiver isenção
        if ($this->tabela == "tbfolga" AND !empty($this->id)) {
            $select .= " AND idFolga <> {$this->id}";
        }

        $select .= " ORDER BY data";

        $afast = $pessoal->select($select, false);

        if (!empty($afast)) {
            $this->afastamento = "Folga";
            $this->detalhe = "Em folga do TRE";
            return true;
        }

        return false;
    }

    ###########################################################
}
