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
                              tbtipovacina.nome
                         FROM tbvacina JOIN tbtipovacina USING (idTipoVacina)                       
                        WHERE idServidor = {$idServidor}
                     ORDER BY data DESC";

            $row = $pessoal->select($select);
            $num = count($row);
        }

        # Exibe as vacinas
        foreach ($row as $item) {
            echo date_to_php($item[0]), " - ", $item[1], "<br/>";
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
                     FROM tbvacina JOIN tbhistlot USING (idServidor)
                                   JOIN tbservidor USING (idServidor)
                                   JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                   JOIN tbperfil USING (idPerfil)
                    WHERE situacao = 1
                      AND tbperfil.tipo <> 'Outros'
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

    public function exibeQuadroVacinas($idLotacao = null) { 
        
        # Acessa o banco de dados
        $pessoal = new Pessoal();
        $lotacao = new Lotacao();
        
        # Subtítulo
        $subtitulo = null;
        
        # Trata a lotação        
        if (!empty($idLotacao) AND ($idLotacao <> "Todos")) {
            if (is_numeric($idLotacao)) {
                $subtitulo = $pessoal->get_nomeLotacao($idLotacao);
            } else {
                $subtitulo = $lotacao->get_nomeDiretoriaSigla($idLotacao);
            }
        }else{
            $idLotacao = null;
        }

        # Pega os dados        
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
        $tabela->set_titulo("Entrega de Comprovantes");
        $tabela->set_subtitulo($subtitulo);
        $tabela->set_label(["Entregaram", "Servidores", "%"]);
        $tabela->set_width([33, 33, 33]);
        $tabela->set_formatacaoCondicional(array(
            array('coluna' => 0,
                'valor' => "Sim",
                'operador' => '=',
                'id' => 'apto'),
            array('coluna' => 0,
                'valor' => "Nâo",
                'operador' => '=',
                'id' => 'naoApto')));

        $tabela->set_colunaSomatorio(1);
        $tabela->set_textoSomatorio("Total:");
        $tabela->set_totalRegistro(false);

        $tabela->show();
    }

    ###########################################################

    public function exibeQuadroAptidao($idLotacao = null) {

        # Acessa o banco de dados
        $pessoal = new Pessoal();
        $lotacao = new Lotacao();
        
        # Subtítulo
        $subtitulo = null;
        
        # Trata a lotação        
        if (!empty($idLotacao) AND ($idLotacao <> "Todos")) {
            if (is_numeric($idLotacao)) {
                $subtitulo = $pessoal->get_nomeLotacao($idLotacao);     
            } else {
                $subtitulo = $lotacao->get_nomeDiretoriaSigla($idLotacao);
            }
        }else{
            $idLotacao = null;
        }

        # Pega os dados
        $numServidores = $pessoal->get_numServidoresAtivos($idLotacao);
        $aptos = $this->getNumServidoresAptos($idLotacao);

        $porcentagemAptos = number_format(($aptos * 100) / $numServidores, 1, '.', '');
        $porcentagemNaoAptos = number_format(100 - $porcentagemAptos, 1, '.', '');

        $array = [
            ["Sim", $aptos, "{$porcentagemAptos} %"],
            ["Nâo", $numServidores - $aptos, "{$porcentagemNaoAptos} %"],
        ];

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($array);
        $tabela->set_titulo("Acessar os Campi da Uenf");
        $tabela->set_subtitulo($subtitulo);
        $tabela->set_label(["Aptos", "Servidores", "%"]);
        $tabela->set_width([33, 33, 33]);
        $tabela->set_formatacaoCondicional(array(
            array('coluna' => 0,
                'valor' => "Sim",
                'operador' => '=',
                'id' => 'apto'),
            array('coluna' => 0,
                'valor' => "Nâo",
                'operador' => '=',
                'id' => 'naoApto')));

        $tabela->set_colunaSomatorio(1);
        $tabela->set_textoSomatorio("Total:");
        $tabela->set_totalRegistro(false);

        $tabela->show();
    }

    ###########################################################

    public function exibeQuadroDosesPorVacina($idLotacao = null) {

        # Acessa o banco de dados
        $pessoal = new Pessoal();
        $lotacao = new Lotacao();
        
        # Subtítulo
        $subtitulo = null;
        
        # Trata a lotação        
        if (!empty($idLotacao) AND ($idLotacao <> "Todos")) {
            if (is_numeric($idLotacao)) {
                $subtitulo = $pessoal->get_nomeLotacao($idLotacao);
            } else {
                $subtitulo = $lotacao->get_nomeDiretoriaSigla($idLotacao);
            }
        }else{
            $idLotacao = null;
        }

        # Geral - Por Cargo
        $select = "SELECT tbtipovacina.nome, count(tbvacina.idServidor) as jj
                     FROM tbservidor LEFT JOIN tbvacina USING (idServidor)
                                     LEFT JOIN tbtipovacina USING (idTipoVacina)
                                          JOIN tbhistlot USING (idServidor)
                                          JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                          JOIN tbperfil USING (idPerfil)
                    WHERE situacao = 1
                      AND tbperfil.tipo <> 'Outros'
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
        $tabela->set_subtitulo($subtitulo);
        $tabela->set_label(["Vacina", "Doses"]);
        $tabela->set_colunaSomatorio(1);
        $tabela->set_textoSomatorio("Total:");
        $tabela->set_totalRegistro(false);

        $tabela->show();
    }

    ###########################################################

    public function exibeQuadroQuantidadeDoses($idLotacao = null, $dosesAptidao = null) {

        # Acessa o banco de dados
        $pessoal = new Pessoal();
        $lotacao = new Lotacao();
        
        # Subtítulo
        $subtitulo = null;
        
        # Trata a lotação        
        if (!empty($idLotacao) AND ($idLotacao <> "Todos")) {
            if (is_numeric($idLotacao)) {
                $subtitulo = $pessoal->get_nomeLotacao($idLotacao);
            } else {
                $subtitulo = $lotacao->get_nomeDiretoriaSigla($idLotacao);
            }
        }else{
            $idLotacao = null;
        }

        # Geral - Por Cargo
        $select = "SELECT count(tbvacina.idServidor)
                     FROM tbservidor LEFT JOIN tbvacina USING (idServidor)
                                     LEFT JOIN tbtipovacina USING (idTipoVacina)
                                          JOIN tbhistlot USING (idServidor)
                                          JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                          JOIN tbperfil USING (idPerfil)
                    WHERE situacao = 1
                      AND tbperfil.tipo <> 'Outros'
                      AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

        # Verifica se tem filtro por lotação
        if (!empty($idLotacao)) {
            if (is_numeric($idLotacao)) {
                $select .= " AND (tblotacao.idlotacao = {$idLotacao})";
            } else { # senão é uma diretoria genérica
                $select .= " AND (tblotacao.DIR = '{$idLotacao}')";
            }
        }

        $select .= " 
                GROUP BY idServidor
                ORDER BY 1 DESC ";

        
        $servidores = $pessoal->select($select);

        # Pega os dados
        $numServidores = $pessoal->get_numServidoresAtivos($idLotacao);

        foreach ($servidores as $tt) {
            $arraySimples[] = $tt[0];
        }

        $arraySimples2 = array_count_values($arraySimples);

        foreach ($arraySimples2 as $key => $value) {
            $arraySimples3[] = [$key . " dose(s)", $value, number_format(($value * 100) / $numServidores, 1, '.', '') . " %"];
        }

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($arraySimples3);
        $tabela->set_titulo("Quantidade de Doses");
        $tabela->set_subtitulo($subtitulo);
        $tabela->set_label(["Doses", "Servidores", "%"]);

        $tabela->set_formatacaoCondicional(array(
            array('coluna' => 0,
                'valor' => $dosesAptidao,
                'operador' => '>=',
                'id' => 'apto'),
            array('coluna' => 0,
                'valor' => $dosesAptidao,
                'operador' => '<',
                'id' => 'naoApto')));

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

    public function exibeJustificativa($idServidor = null) {

        if (empty($idServidor)) {
            return null;
        } else {

            # Monta o select
            $select = "SELECT justificativaVacina
                         FROM tbservidor
                        WHERE idServidor = {$idServidor}";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select, false);

            if (empty($row[0])) {
                return null;
            } else {
                $painel = new Callout();
                $painel->abre();

                tituloTable("Justificativa enviada pelo Servidor");
                br();
                p(nl2br($row[0]), "left", "f14");

                $painel->fecha();
            }
        }
    }

    ###########################################################

    public function getNumServidoresAptos($idLotacao = null) {
        /**
         * Retorna o nome da lotação
         * 
         * @syntax $this->get_dados($idRpa);
         */
        # Monta o select
        $select = "SELECT DISTINCT rr.idServidor
                     FROM tbservidor as rr JOIN tbpessoa USING (idPessoa)
                                           JOIN tbhistlot USING (idServidor)
                                           JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = rr.idServidor)
                          AND (SELECT COUNT(idServidor) FROM tbvacina as tt WHERE tt.idServidor = rr.idServidor) > 2";

        # Verifica se tem filtro por lotação
        if (!empty($idLotacao) AND ($idLotacao <> "Todos")) {
            if (is_numeric($idLotacao)) {
                $select .= " AND (tblotacao.idlotacao = {$idLotacao})";
            } else {
                $select .= " AND (tblotacao.DIR = '{$idLotacao}')";
            }
        }

        $pessoal = new Pessoal();
        $num = $pessoal->count($select);
        return $num;
    }

    ###########################################################
}
