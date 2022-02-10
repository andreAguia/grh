<?php

class Vacina {

    /**
     * Classe que abriga as várias rotina de Lotação
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ##############################################################

    public function get_dados($idVacina = null) {

        /**
         * Informa os dados da base de dados
         * 
         * @param $idVacina integer null O id 
         * 
         * @syntax $vacina->get_dados([$idVacina]);
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if (vazio($idVacina)) {
            alert("É necessário informar o id da vacina.");
            return;
        }

        # Pega os dados
        $select = "SELECT * 
                     FROM tbvacina
                    WHERE idVacina = {$idVacina}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        return $row;
    }

###########################################################

    public function exibeVacinas($idServidor = null) {
        /**
         * Retorna o nome da lotação
         * 
         * @syntax $this->get_dados($idRpa);
         */
        if (empty($idServidor)) {
            return null;
        } else {
            $pessoal = new Pessoal();

            # Pega os dados
            $select = "SELECT data,
                              tbtipovacina.nome,
                              comprovante
                       FROM tbvacina JOIN tbtipovacina USING (idTipoVacina)                       
                      WHERE idServidor = {$idServidor}
                   ORDER BY data";

            $row = $pessoal->select($select);
            $num = count($row);
        }

        # Exibe as vacinas
        if ($num == 0) {
            p("Não Informado", "pVacinaNInformada");
        } elseif ($num == 1) {
            if (empty($row[0][0])) {
                p("Data não Informada - " . $row[0][1], "pVacinaUmaDose");
            } else {
                if ($row[0][2]) {
                    p(date_to_php($row[0][0]) . " - COM COMPROVANTE - " . $row[0][1], "pVacinaUmaDose");
                } else {
                    p(date_to_php($row[0][0]) . " - SEM COMPROVANTE - " . $row[0][1], "pVacinaUmaDose");
                }
            }
        } else {
            foreach ($row as $item) {
                if (empty($item[0])) {
                    p("Data não Informada - " . $item[1], "pVacinaUmaDose");
                } else {
                    if ($item[2]) {
                        echo date_to_php($item[0]), " - COM COMPROVANTE - ", $item[1], "<br/>";
                    } else {
                        echo date_to_php($item[0]), " - SEM COMPROVANTE - ", $item[1], "<br/>";
                    }
                }
            }
        }
    }

###########################################################

    public function getNumServidoresAtivosVacinados($idLotacao = null, $idTipoVacina = null) {
        /**
         * Retorna o nome da lotação
         * 
         * @syntax $this->get_dados($idRpa);
         */
        # Monta o select
        $select = "SELECT DISTINCT tbvacina.idServidor
                     FROM tbvacina 
                     JOIN tbhistlot USING (idServidor)
                     JOIN tbservidor USING (idServidor)
                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                    WHERE situacao = 1
                      AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

        # Verifica se tem filtro por lotação
        if (!empty($idLotacao) AND ($idLotacao <> "Todos")) {
            if (is_numeric($idLotacao)) {
                $select .= " AND (tblotacao.idlotacao = {$idLotacao})";
            } else {
                $select .= " AND (tblotacao.DIR = '{$idLotacao}')";
            }
        }

        # Verifica se tem filtro por vacina
        if (!empty($idTipoVacina) AND ($idTipoVacina <> "Todos")) {
            $select .= " AND idTipoVacina = {$idTipoVacina}";
        }

        $pessoal = new Pessoal();
        $num = $pessoal->count($select);
        return $num;
    }

    ###########################################################

    public function getNumDoses($idLotacao = null, $idTipoVacina = null) {
        /**
         * Retorna o nome da lotação
         * 
         * @syntax $this->get_dados($idRpa);
         */
        # Monta o select
        $select = "SELECT tbvacina.idServidor
                     FROM tbvacina 
                     JOIN tbhistlot USING (idServidor)
                     JOIN tbservidor USING (idServidor)
                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                    WHERE situacao = 1
                      AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

        # Verifica se tem filtro por lotação
        if (!empty($idLotacao) AND ($idLotacao <> "Todos")) {
            if (is_numeric($idLotacao)) {
                $select .= " AND (tblotacao.idlotacao = {$idLotacao})";
            } else {
                $select .= " AND (tblotacao.DIR = '{$idLotacao}')";
            }
        }

        # Verifica se tem filtro por vacina
        if (!empty($idTipoVacina) AND ($idTipoVacina <> "Todos")) {
            $select .= " AND idTipoVacina = {$idTipoVacina}";
        }

        $pessoal = new Pessoal();
        $num = $pessoal->count($select);
        return $num;
    }

    ###########################################################

    public function getNumDosesComprovadas($idLotacao = null, $idTipoVacina = null) {
        /**
         * Retorna o nome da lotação
         * 
         * @syntax $this->get_dados($idRpa);
         */
        # Monta o select
        $select = "SELECT tbvacina.idServidor
                     FROM tbvacina 
                     JOIN tbhistlot USING (idServidor)
                     JOIN tbservidor USING (idServidor)
                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                    WHERE situacao = 1
                      AND comprovante
                      AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

        # Verifica se tem filtro por lotação
        if (!empty($idLotacao) AND ($idLotacao <> "Todos")) {
            if (is_numeric($idLotacao)) {
                $select .= " AND (tblotacao.idlotacao = {$idLotacao})";
            } else {
                $select .= " AND (tblotacao.DIR = '{$idLotacao}')";
            }
        }

        # Verifica se tem filtro por vacina
        if (!empty($idTipoVacina) AND ($idTipoVacina <> "Todos")) {
            $select .= " AND idTipoVacina = {$idTipoVacina}";
        }

        $pessoal = new Pessoal();
        $num = $pessoal->count($select);
        return $num;
    }

    ###########################################################

    public function getNumDosesNaoComprovadas($idLotacao = null, $idTipoVacina = null) {
        /**
         * Retorna o nome da lotação
         * 
         * @syntax $this->get_dados($idRpa);
         */
        # Monta o select
        $select = "SELECT tbvacina.idServidor
                     FROM tbvacina 
                     JOIN tbhistlot USING (idServidor)
                     JOIN tbservidor USING (idServidor)
                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                    WHERE situacao = 1
                      AND NOT comprovante
                      AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

        # Verifica se tem filtro por lotação
        if (!empty($idLotacao) AND ($idLotacao <> "Todos")) {
            if (is_numeric($idLotacao)) {
                $select .= " AND (tblotacao.idlotacao = {$idLotacao})";
            } else {
                $select .= " AND (tblotacao.DIR = '{$idLotacao}')";
            }
        }

        # Verifica se tem filtro por vacina
        if (!empty($idTipoVacina) AND ($idTipoVacina <> "Todos")) {
            $select .= " AND idTipoVacina = {$idTipoVacina}";
        }

        $pessoal = new Pessoal();
        $num = $pessoal->count($select);
        return $num;
    }

    ###########################################################

    public function exibeQuadroVacinas($idLotacao = null) {

        # Trata os dados
        if ($idLotacao == "Todos") {
            $idLotacao = null;
        }

        # Pega os dados
        $pessoal = new Pessoal();
        $numServidores = $pessoal->get_numServidoresAtivos($idLotacao);
        $vacinados = $this->getNumServidoresAtivosVacinados($idLotacao);

        $porcentagemVacinados = number_format(($vacinados * 100) / $numServidores, 1, '.', '');
        $porcentagemNaoVacinados = number_format(100 - $porcentagemVacinados, 1, '.', '');

        $array = [
            ["Sim", $vacinados, "{$porcentagemVacinados} %"],
            ["Nâo", $numServidores - $vacinados, "{$porcentagemNaoVacinados} %"],
        ];

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($array);
        $tabela->set_titulo("Quadro da Vacina");
        $tabela->set_label(["Vacinados", "Servidores", "%"]);
        $tabela->set_width([33, 33, 33]);
        #$tabela->set_align(["left"]);

        $tabela->set_colunaSomatorio(1);
        $tabela->set_textoSomatorio("Total:");
        $tabela->set_totalRegistro(false);

        $tabela->show();
    }

    ###########################################################

    public function exibeQuadroDosesPorVacina($idLotacao = null) {

        # Trata os dados
        if ($idLotacao == "Todos") {
            $idLotacao = null;
        }

        # Geral - Por Cargo
        $select = "SELECT tbtipovacina.nome, count(tbvacina.idServidor) as jj
                     FROM tbservidor LEFT JOIN tbvacina USING (idServidor)
                                     LEFT JOIN tbtipovacina USING (idTipoVacina)
                                          JOIN tbhistlot USING (idServidor)
                                          JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                               WHERE situacao = 1
                                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

        # Verifica se tem filtro por lotação
        if (!empty($idLotacao)) {
            if (is_numeric($idLotacao)) {
                $select .= " AND (tblotacao.idlotacao = {$idLotacao})";
            } else { # senão é uma diretoria genérica
                $select .= " AND (tblotacao.DIR = '{$idLotacao}')";
            }
        }

        $select .= " AND tbtipovacina.nome IS NOT null
                GROUP BY tbtipovacina.nome
                ORDER BY 2 DESC ";

        #echo $select;

        $pessoal = new Pessoal();
        $servidores = $pessoal->select($select);

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($servidores);
        $tabela->set_titulo("Doses por Vacina");
        $tabela->set_label(["Vacina", "Doses"]);
        #$tabela->set_width(array(30, 15, 15, 15, 15));
        $tabela->set_align(["left"]);

        $tabela->set_colunaSomatorio(1);
        $tabela->set_textoSomatorio("Total:");
        $tabela->set_totalRegistro(false);

        $tabela->show();
    }

    ###########################################################

    public function exibeQuadroVacinadosNComprovados($idLotacao = null) {

        # Trata os dados
        if ($idLotacao == "Todos") {
            $idLotacao = null;
        }

        # Pega os servidores que tem doses não comprovadas
        $select = "SELECT DISTINCT tbvacina.idServidor
                     FROM tbvacina LEFT JOIN tbservidor USING (idServidor)
                                        JOIN tbhistlot USING (idServidor)
                                        JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                     WHERE situacao = 1
                       AND NOT comprovante 
                       AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

        # Verifica se tem filtro por lotação
        if (!empty($idLotacao)) {
            if (is_numeric($idLotacao)) {
                $select .= " AND (tblotacao.idlotacao = {$idLotacao})";
            } else { # senão é uma diretoria genérica
                $select .= " AND (tblotacao.DIR = '{$idLotacao}')";
            }
        }
        
        $pessoal = new Pessoal();
        $Ncomprovadas = $pessoal->count($select);
        $vacinados = $this->getNumServidoresAtivosVacinados($idLotacao);
        $porcentagemNaoComprovadas = number_format(($Ncomprovadas * 100) / $vacinados, 1, '.', '');

        $servidores = [
            [$Ncomprovadas, $porcentagemNaoComprovadas]
        ];

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($servidores);
        $tabela->set_titulo("Servidores com Alguma Dose Não Comprovados");
        $tabela->set_label(["Servidores", "%"]);
        $tabela->set_width([33, 33, 33]);

//        $tabela->set_colunaSomatorio(1);
//        $tabela->set_textoSomatorio("Total:");
        $tabela->set_totalRegistro(false);

        $tabela->show();
    }

    ###########################################################

    public function exibeQuadroDosesPorComprovante($idLotacao = null) {

        # Trata os dados
        if ($idLotacao == "Todos") {
            $idLotacao = null;
        }

        # Pega os dados
        $comprovadas = $this->getNumDosesComprovadas($idLotacao);
        $Ncomprovadas = $this->getNumDosesNaoComprovadas($idLotacao);
        $total = $this->getNumDoses($idLotacao);

        $porcentagemComprovadas = number_format(($comprovadas * 100) / $total, 1, '.', '');
        $porcentagemNaoComprovadas = number_format(($Ncomprovadas * 100) / $total, 1, '.', '');

        $servidores = [
            ["Sim", $comprovadas, $porcentagemComprovadas],
            ["Não", $Ncomprovadas, $porcentagemNaoComprovadas]
        ];

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($servidores);
        $tabela->set_titulo("Doses por Comprovação");
        $tabela->set_label(["Comprovadas", "Doses", "%"]);
        $tabela->set_width([33, 33, 33]);

        $tabela->set_colunaSomatorio(1);
        $tabela->set_textoSomatorio("Total:");
        $tabela->set_totalRegistro(false);

        $tabela->show();
    }

    ###########################################################

    public function getNumServidoresAtivosVacinadosVacina($idTipoVacina = null) {

        # Monta o select
        $select = "SELECT DISTINCT tbvacina.idServidor
                     FROM tbvacina JOIN tbservidor USING (idServidor)
                    WHERE tbservidor.situacao = 1 
                      AND idTipoVacina = {$idTipoVacina}";

        $pessoal = new Pessoal();
        $num = $pessoal->count($select);
        return $num;
    }

    ###########################################################

    public function getNumServidoresInativosVacinadosVacina($idTipoVacina = null) {

        # Monta o select
        $select = "SELECT DISTINCT tbvacina.idServidor
                     FROM tbvacina JOIN tbservidor USING (idServidor)
                    WHERE tbservidor.situacao <> 1 
                      AND idTipoVacina = {$idTipoVacina}";

        $pessoal = new Pessoal();
        $num = $pessoal->count($select);
        return $num;
    }

    ###########################################################

    public function getNumServidoresGeralVacinadosVacina($idTipoVacina = null) {

        # Monta o select
        $select = "SELECT DISTINCT tbvacina.idServidor
                     FROM tbvacina JOIN tbservidor USING (idServidor)
                    WHERE idTipoVacina = {$idTipoVacina}";

        $pessoal = new Pessoal();
        $num = $pessoal->count($select);
        return $num;
    }

    ###########################################################
}
