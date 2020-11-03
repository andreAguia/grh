<?php

class RpaPrestador
{

    /**
     * Classe que abriga as várias rotina dos Prestadores de serviço
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ###########################################################

    public function get_dados($idPrestador = null)
    {
        /**
         * Retorna todos os dados 
         * 
         * @syntax $this->get_dados($idRpa);
         */
        if (empty($idPrestador)) {
            return null;
        } else {
            # Pega os dados
            $select = "SELECT *,
                              tbcidade.nome as cidade,
                              tbestado.uf as estado
                       FROM tbrpa_prestador LEFT JOIN tbcidade USING (idCidade)
                                            LEFT JOIN tbestado USING (idEstado)
                      WHERE idPrestador = $idPrestador";

            $pessoal = new Pessoal();
            return $pessoal->select($select, false, true);
        }
    }

    ##############################################################

    public function exibePrestador($idPrestador)
    {
        /*
         * Exibe Prestador e o CPF
         */

        # Pega os dados 
        $dados = $this->get_dados($idPrestador);

        p($dados["prestador"], "pvalor");
        p($dados["especialidade"], "paliquota");
    }

    ##############################################################

    public function getIdPrestador($cpf)
    {
        /*
         * informa o idPrestador pelo cpf
         */

        # Pega os dados
        $select = "SELECT idPrestador
                       FROM tbrpa_prestador
                      WHERE cpf = '{$cpf}'";

        $pessoal = new Pessoal();
        $retorno = $pessoal->select($select, false, true);

        if (empty($retorno["idPrestador"])) {
            return null;
        } else {
            return $retorno["idPrestador"];
        }
    }

    ##########################################################################################

    public function get_emails($idPrestador)
    {

        # Função que retorna os emails do prestador
        #
        # Parâmetro: $idPrestador

        $select = "SELECT email
                    FROM tbrpa_prestador
                   WHERE idPrestador = {$idPrestador}";

        $pessoal = new Pessoal();
        $retorno = $pessoal->select($select, false, true);

        return $retorno["email"];
    }

    ##########################################################################################

    public function get_telefones($idPrestador)
    {

        # Função que retorna os telefones 
        #
        # Parâmetro: $idPrestador

        $select = "SELECT telefone1,
                          telefone2
                         FROM tbrpa_prestador
                         WHERE idPrestador = {$idPrestador}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false, true);
        $retorno = null;

        if (!empty($row["telefone1"])) {
            $retorno .= "{$row['telefone1']}<br/>";
        }

        if (!empty($row["telefone2"])) {
            $retorno .= "{$row['telefone2']}<br/>";
        }

        return $retorno;
    }

    ##########################################################################################

    public function exibeContatos($idPrestador)
    {

        # Função que retorna os contatos
        #
        # Parâmetro: $idPrestador

        echo $this->get_telefones($idPrestador);
        echo $this->get_emails($idPrestador);
    }

    ##########################################################################################

    public function exibeEndereco($idPrestador)
    {

        # Função que retorna os telefones 
        #
        # Parâmetro: $idPrestador

        $select = "SELECT endereco,
                          bairro,
                          nome,
                          cep
                         FROM tbrpa_prestador LEFT JOIN tbcidade USING (idCidade)
                         WHERE idPrestador = {$idPrestador}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false, true);
        $retorno = null;

        if (!empty($row["endereco"])) {
            $retorno .= "{$row['endereco']}<br/>";
        }

        if (!empty($row["bairro"])) {
            $retorno .= "{$row['bairro']}<br/>";
        }

        if (!empty($row["nome"])) {
            $retorno .= "{$row['nome']}<br/>";
        }

        if (!empty($row["cep"])) {
            $retorno .= "Cep: {$row['cep']}<br/>";
        }

        return $retorno;
    }

    ##########################################################################################

    public function exibeDocumentos($idPrestador)
    {

        # Função que retorna os telefones 
        #
        # Parâmetro: $idPrestador

        $select = "SELECT identidade,
                          orgaoId,
                          dataId,
                          inss
                     FROM tbrpa_prestador
                    WHERE idPrestador = {$idPrestador}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false, true);
        $retorno = null;

        if (!empty($row["identidade"])) {
            $retorno .= "Id: {$row['identidade']}<br/>";
        }

        if (!empty($row["orgaoId"])) {
            $retorno .= "Órgão: {$row['orgaoId']}<br/>";
        }

        if (!empty($row["dataId"])) {
            $retorno .= "Emitido em: ".date_to_php($row['dataId'])."<br/>";
        }

        if (!empty($row["inss"])) {
            $retorno .= "PIS/PASEP: {$row['inss']}";
        }

        return $retorno;
    }

    ##########################################################################################
}
