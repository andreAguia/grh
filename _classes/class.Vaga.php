<?php

class Vaga {

    /**
     * Classe que abriga as várias rotina do Controle de Vagas de Docentes
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ###########################################################

    public function get_dados($idVaga = null) { // integer o id da vaga
        /**
         * Retorna todos os dados arquivados na tabela tbvaga
         * 
         * @syntax $this->get_dados($idVaga);
         */

        if (vazio($idVaga)) {
            alert("Deve-se informar o idVaga!");
        } else {
            # Pega os dados
            $select = "SELECT *
                       FROM tbvaga
                      WHERE idVaga = {$idVaga}";

            $pessoal = new Pessoal();
            return $pessoal->select($select, false);
        }
    }

    ###########################################################

    /**
     * Método exibeVagasOcupadas
     */
    function exibeVagasOcupadas($centro = null) {

        # Conecta o banco
        $pessoal = new Pessoal();

        # Inicia as variáveis
        $resultado = array();

        # Faz os cálculos
        $ocupadoTitular = $this->get_numVagasCargoDiretoriaOcupados(129, $centro);
        $ocupadoAssociado = $this->get_numVagasCargoDiretoriaOcupados(128, $centro);

        $resultado[] = array("Professor Titular", $ocupadoTitular);
        $resultado[] = array("Professor Associado", $ocupadoAssociado);
        $resultado[] = array("Total", $ocupadoAssociado + $ocupadoTitular);

        $titulo = "Vagas Ocupadas";

        if (!vazio($centro)) {
            $titulo .= " do $centro";
        }

        $tabela = new Tabela();
        $tabela->set_titulo($titulo);
        $tabela->set_conteudo($resultado);
        $tabela->set_label(array("Cargo", "Quantidade"));
        #$tabela->set_width(array());
        $tabela->set_totalRegistro(false);
        $tabela->set_align(array("left", "center"));
        $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                'valor' => "Total",
                'operador' => '=',
                'id' => 'totalVagas')));
        $tabela->show();
    }

    ###########################################################

    /**
     * Método exibeVagasDisponiveis
     */
    function exibeVagasDisponiveis($centro = null) {

        # Conecta o banco
        $pessoal = new Pessoal();

        # Inicia as variáveis
        $resultado = array();

        # Faz os cálculos
        $ocupadoTitular = $this->get_numVagasCargoDiretoriaDisponiveis(129, $centro);
        $ocupadoAssociado = $this->get_numVagasCargoDiretoriaDisponiveis(128, $centro);

        $resultado[] = array("Professor Titular", $ocupadoTitular);
        $resultado[] = array("Professor Associado", $ocupadoAssociado);
        $resultado[] = array("Total", $ocupadoAssociado + $ocupadoTitular);

        $titulo = "Vagas Disponíveis";

        if (!vazio($centro)) {
            $titulo .= " do $centro";
        }

        $tabela = new Tabela();
        $tabela->set_titulo($titulo);
        $tabela->set_conteudo($resultado);
        $tabela->set_label(array("Cargo", "Quantidade"));
        #$tabela->set_width(array());
        $tabela->set_totalRegistro(false);
        $tabela->set_align(array("left", "center"));
        $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                'valor' => "Total",
                'operador' => '=',
                'id' => 'totalVagas')));
        $tabela->show();
    }

    ###########################################################

    /**
     * Método exibeVagasGerais
     */
    function exibeVagasGerais($centro = null) {

        # Conecta o banco
        $pessoal = new Pessoal();

        tituloTable($centro);

        $titularDisp = $this->get_numVagasCargoDiretoriaDisponiveis(129, $centro);
        $titularOcup = $this->get_numVagasCargoDiretoriaOcupados(129, $centro);
        $titularTotal = $titularDisp + $titularOcup;

        $associaDisp = $this->get_numVagasCargoDiretoriaDisponiveis(128, $centro);
        $associaOcup = $this->get_numVagasCargoDiretoriaOcupados(128, $centro);
        $associaTotal = $associaDisp + $associaOcup;

        $diponiTotal = $associaDisp + $titularDisp;
        $ocupadoTotal = $associaOcup + $titularOcup;

        $arrayResult = array(array("Professor Titular", $titularDisp, $titularOcup, $titularTotal),
            array("Professor Associado", $associaDisp, $associaOcup, $associaTotal),
            array("Total", $diponiTotal, $ocupadoTotal, $ocupadoTotal + $diponiTotal));

        $arrayTitular = array(array("Ocupada", $titularOcup),
            array("Disponível", $titularDisp));

        $arrayAssociado = array(array("Ocupada", $associaOcup),
            array("Disponível", $associaDisp));

        # Tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($arrayResult);
        #$tabela->set_titulo($cc);
        $tabela->set_label(array("Cargo", "Disponíveis", "Ocupadas", "Total"));
        $tabela->set_width(array(40, 20, 20, 20));
        $tabela->set_align(array("left"));
        $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                'valor' => "Total",
                'operador' => '=',
                'id' => 'estatisticaTotal')));
        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

    ###########################################################

    /**
     * Método get_numVagasCargoDiretoriaDisponiveis
     * fornece o número de vagas cadastradas para um determinado cargo (Titular/Associado) para uma determinada diretoria
     */
    function get_numVagasCargoDiretoriaDisponiveis($idCargo = null, $dir = null) {

        # Conecta o banco
        $pessoal = new Pessoal();

        # Inicia o contador
        $disponivel = 0;

        # Pega as vagas desse cargo e desse centro
        $select = "SELECT idVaga
                     FROM tbvaga
                    WHERE true";

        if (!vazio($idCargo)) {
            $select .= " AND idCargo = $idCargo";
        }

        if (!vazio($dir)) {
            $select .= " AND centro = '$dir'";
        }


        $dado = $pessoal->select($select);

        # Percorre os resultados
        foreach ($dado as $dd) {
            $idServidor = $this->get_idServidorOcupante($dd[0]);

            # Se não tiver nenhum candidato
            if (empty($idServidor)) {
                $disponivel++;
            } else {
                $situacao = $pessoal->get_situacao($idServidor);

                # Compara de é inativo
                if ($situacao <> "Ativo") {
                    $disponivel++;
                }
            }
        }
        return $disponivel;
    }

    ###########################################################

    /**
     * Método get_numVagasCargoDiretoriaOcupados
     * fornece o número de vagas cadastradas para um determinado cargo (Titular/Associado) para uma determinada diretoria
     * 
     * @param	string $idVaga O id da vaga do servidor
     */
    function get_numVagasCargoDiretoriaOcupados($idCargo = null, $dir = null) {

        # Conecta o banco
        $pessoal = new Pessoal();

        # Inicia o contador
        $ocupado = 0;

        # Pega as vagas desse cargo e desse centro
        $select = "SELECT idVaga
                     FROM tbvaga
                     WHERE true";

        if (!vazio($idCargo)) {
            $select .= " AND idCargo = $idCargo";
        }

        if (!vazio($dir)) {
            $select .= " AND centro = '$dir'";
        }

        $dado = $pessoal->select($select);

        # Percorre os resultados
        foreach ($dado as $dd) {
            $idServidor = $this->get_idServidorOcupante($dd[0]);

            # Se não tiver nenhum candidato
            if (!empty($idServidor)) {
                $situacao = $pessoal->get_situacao($idServidor);

                # Compara se está ocupado
                if ($situacao == "Ativo") {
                    $ocupado++;
                }
            }
        }
        return $ocupado;
    }

    ###########################################################

    /**
     * Método get_servidorOcupante
     * fornece o nome do servidor ocupante da último edital para esta vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */
    function get_servidorOcupante($idVaga) {

        if (is_numeric($idVaga)) {

            # Pega os dados
            $pessoal = new Pessoal();

            $select = "SELECT idServidor,
                              idConcurso,
                              idLotacao,
                              area,
                              tbvagahistorico.obs
                         FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                        WHERE idVaga = {$idVaga} ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1";

            $dados = $pessoal->select($select, false);

            if (empty($dados)) {
                return null;
            } else {
                return $this->get_nome($dados["idServidor"]);
            }
        } else {
            return $idVaga;
        }
    }

    ###########################################################

    /**
     * Método get_nome
     * fornece o nome do servidor e outros dados de uma idServidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    function get_nome($idServidor) {

        if (is_numeric($idServidor)) {

            # Conecta o banco
            $pessoal = new Pessoal();

            if ($pessoal->get_idSituacao($idServidor) == 1) {
                $css = 'vagasAtivo';
            } else {
                $css = 'vagasInativo';
            }

            p($pessoal->get_nome($idServidor), $css);
            p($pessoal->get_dtAdmissao($idServidor) . "  -  " . $pessoal->get_dtSaida($idServidor), $css);
            p($pessoal->get_lotacaoSimples($idServidor), $css);
            if (!vazio($pessoal->get_cargoComissao($idServidor))) {
                p($pessoal->get_cargoComissao($idServidor), $css);
            }
        } else {
            return $idServidor;
        }
    }

    ###########################################################

    /**
     * Método get_nomeRel
     * fornece o nome do servidor e outros dados de uma idServidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    function get_nomeRel($idServidor) {

        if (is_numeric($idServidor)) {

            # Conecta o banco
            $pessoal = new Pessoal();

            # Pega os dados
            $nome = $pessoal->get_nome($idServidor);
            $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);
            $idSiituacao = $pessoal->get_idSituacao($idServidor);
            $dtSaida = $pessoal->get_dtSaida($idServidor);
            $lotacao = $pessoal->get_lotacaoSimples($idServidor);
            $comissao = $pessoal->get_cargoComissao($idServidor);

            if ($idSiituacao == 1) {
                $nome = "<b>$nome</b>";
            }

            echo $nome;
            br();
            echo $dtAdmissao . "  -  " . $dtSaida;
            br();
            echo $lotacao;
            br();

            if (!vazio($comissao)) {
                echo "$comissao";
            }
        }
    }

    ###########################################################

    /**
     * Método get_idServidorOcupante
     * fornece o nome do servidor ocupante da último edital para esta vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */
    function get_idServidorOcupante($idVaga) {

        # Pega os dados
        $pessoal = new Pessoal();

        $select = 'SELECT idServidor,
                          idConcurso,
                          idLotacao,
                          area,
                          tbvagahistorico.obs
                     FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                    WHERE idVaga = ' . $idVaga . ' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';

        $dados = $pessoal->select($select, false);

        if (empty($dados["idServidor"])) {
            return null;
        } else {
            return $dados["idServidor"];
        }
    }

    ##########################################################

    /**
     * Método get_idServidoresOcupantes
     * fornece oum array com todos os servidores que ocuparam essa vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */
    function get_idServidoresOcupantes($idVaga) {

        # Pega os dados
        $pessoal = new Pessoal();

        $select = 'SELECT idServidor
                     FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                    WHERE idVaga = ' . $idVaga . ' ORDER BY tbconcurso.dtPublicacaoEdital';

        $dados = $pessoal->select($select);
        return $dados;
    }

    ###########################################################

    /**
     * Método get_concursoOcupante
     * fornece o nome do concurso de servidor ocupante da último edital para esta vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */
    function get_concursoOcupante($idVaga) {
        if (is_numeric($idVaga)) {

            # Conecta o banco
            $pessoal = new Pessoal();

            $select = 'SELECT concat(anoBase," - Edital: ",DATE_FORMAT(dtPublicacaoEdital,"%d/%m/%Y"))
                         FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                        WHERE idVaga = ' . $idVaga . ' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';

            $dado = $pessoal->select($select, false);
            return $dado[0];
        } else {
            return $idVaga;
        }
    }

    ###########################################################

    /**
     * Método get_areaOcupante
     * fornece area do concurso de servidor ocupante da último edital para esta vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */
    function get_areaOcupante($idVaga) {
        if (is_numeric($idVaga)) {

            # Conecta o banco
            $pessoal = new Pessoal();

            $select = 'SELECT area
                         FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                        WHERE idVaga = ' . $idVaga . ' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';

            $dado = $pessoal->select($select, false);
            return $dado[0];
        } else {
            return $idVaga;
        }
    }

    ###########################################################

    /**
     * Método get_obsOcupante
     * fornece a obs da vaga do servidor ocupante da último edital para esta vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */
    function get_obsOcupante($idVaga) {
        if (is_numeric($idVaga)) {

            # Conecta o banco
            $pessoal = new Pessoal();

            $select = 'SELECT tbvagahistorico.obs
                         FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                        WHERE idVaga = ' . $idVaga . ' ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1';

            $dado = $pessoal->select($select, false);
            return $dado[0];
        } else {
            return $idVaga;
        }
    }

    ###########################################################

    /**
     * Método get_status
     * fornece o status da vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */
    function get_status($idVaga) {

        if (is_numeric($idVaga)) {

            # Conecta o banco
            $pessoal = new Pessoal();

            $select = "SELECT idServidor
                         FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                        WHERE idVaga = {$idVaga} ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1";

            $dado = $pessoal->select($select, false);

            if (empty($dado[0])) {
                return 'Disponível';
            } else {
                # Pega a situação
                $idSituacao = $pessoal->get_idSituacao($dado[0]);

                if ($idSituacao == 1) {
                    return 'Ocupada';
                } else {
                    return 'Disponível';
                }
            }
        } else {
            return 'Disponível';
        }
    }

    ###########################################################

    /**
     * Método get_idCargoVaga
     * fornece o idCargo de uma vaga
     * 
     * @param	string $idVaga O id da vaga
     */
    function get_idCargoVaga($idVaga) {
        if (is_numeric($idVaga)) {

            # Conecta o banco
            $pessoal = new Pessoal();

            $select = 'SELECT idCargo
                         FROM tbvaga
                        WHERE idVaga = ' . $idVaga;

            $dado = $pessoal->select($select, false);
            return $dado[0];
        } else {
            return "---";
        }
    }

    ###########################################################

    /**
     * Método exibeDadosVaga
     * fornece os dados de uma vaga 
     * 
     * @param	string $idVaga O id da vaga
     */
    function exibeDadosVaga($idVaga) {

        # Conecta com o banco de dados
        $servidor = new Pessoal();

        $conteudo = $this->get_dados($idVaga);

        $painel = new Callout("secondary");
        $painel->abre();

        p("Vaga nº:", "vagaIdLabel");
        p($idVaga, "vagaId");

        $painel->fecha();

        $painel = new Callout("primary");
        $painel->abre();

        $btnEditar = new Link("Editar", "cadastroVagas.php?fase=editar&id=$idVaga");
        $btnEditar->set_class('button tiny secondary');
        $btnEditar->set_id('editarVaga');
        $btnEditar->set_title('Editar o Procedimento');
        $btnEditar->show();

        $centro = $conteudo["centro"];
        $idCargo = $conteudo["idCargo"];

        $labOrigem = $servidor->get_nomeLotacao3($this->get_laboratorioOrigem($idVaga));

        $cargo = $servidor->get_nomeCargo($idCargo);
        $status = $this->get_status($idVaga);

        p($centro, "vagaCentro");
        p($cargo, "vagaCargo");

        $title = "O primeiro laboratório da vaga, para o qual a vaga foi criada,";

        p("Laboratório de Origem:", "vagaOrigem", null, $title);
        p($labOrigem, "vagaCargo", null, $title);

        $painel->fecha();

        $painel = new Callout();
        $painel->abre();

        if ($status == "Disponível") {
            tituloTable("Vaga Disponível");
            br();
            p("Ninguém ocupa esta vaga atualmente!", "center", "f14");
            br();
        } else {
            tituloTable("Ocupante Atual");
            br();
            $ocupante = $this->get_servidorOcupante($idVaga);
        }

        $painel->fecha();
    }

    ###########################################################

    /**
     * Método exibeDadosVagaRelatorio
     * fornece os dados de uma vaga 
     * 
     * @param	string $idVaga O id da vaga
     */
    function exibeDadosVagaRelatorio($idVaga) {

        # Conecta com o banco de dados
        $servidor = new Pessoal();

        $conteudo = $this->get_dados($idVaga);
        $centro = $conteudo["centro"];
        $labOrigem = $servidor->get_nomeLotacao3($this->get_laboratorioOrigem($idVaga));
        $idCargo = $conteudo["idCargo"];
        $cargo = $servidor->get_nomeCargo($idCargo);
        $status = "Vaga {$this->get_status($idVaga)}";

        return "Vaga: {$idVaga}<br/>{$centro}<br/>{$cargo}<br/>{$labOrigem}<br/>{$status}";
    }

    ###########################################################

    /**
     * Método get_numConcursoVaga
     * fornece o status da vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */
    function get_numConcursoVaga($idVaga) {

        # Conecta o banco
        $pessoal = new Pessoal();

        $select = 'SELECT idServidor
                     FROM tbvagahistorico JOIN tbconcurso USING (idConcurso)
                    WHERE idVaga = ' . $idVaga . ' ORDER BY tbconcurso.dtPublicacaoEdital desc';

        $dado = $pessoal->count($select);
        return $dado;
    }

    ###########################################################

    /**
     * Método get_numVagasCargoDiretoria
     * fornece o número de vagas cadastradas para um determinado cargo (Titular/Associado) para uma determinada diretoria
     * 
     * @param	string $idVaga O id da vaga do servidor
     */
    function get_numVagasDiretoria($dir = null) {

        # Conecta o banco
        $pessoal = new Pessoal();

        $select = "SELECT idVaga
                     FROM tbvaga";

        if (!vazio($dir)) {
            $select .= " WHERE centro = '$dir'";
        }

        $dado = $pessoal->count($select);
        return $dado;
    }

    ###########################################################

    /**
     * Método get_numVagasCargoDiretoria
     * fornece o número de vagas cadastradas para um determinado cargo (Titular/Associado) para uma determinada diretoria
     * 
     * @param	string $idVaga O id da vaga do servidor
     */
    function get_numVagasCargoDiretoria($idCargo, $dir = null) {

        # Conecta o banco
        $pessoal = new Pessoal();

        $select = "SELECT idVaga
                     FROM tbvaga
                   WHERE idCargo = $idCargo";

        if (!vazio($dir)) {
            $select .= " AND centro = '$dir'";
        }

        $dado = $pessoal->count($select);
        return $dado;
    }

    ###########################################################

    /**
     * Método exibeTotalVagas
     * fornece o status da vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */
    function exibeTotalVagas($centro = null, $tipo = null) {

        # Conecta o banco
        $pessoal = new Pessoal();

        # Pega as diretorias
        if (vazio($centro)) {
            $diretorias = array("CCT", "CCTA", "CCH", "CBB");
        } else {
            $diretorias[] = $centro;
        }

        # Pega os Cargos
        $cargos = array(128, 129);
        $numeroCargos = count($cargos);

        # Cria um array onde terá os resultados
        $resultado = array();

        # Cria e preenche o array do total da coluna
        $totalColuna = array();
        $totalColuna = array_fill(0, $numeroCargos + 2, 0);

        $totalColunaDisponivel = array();
        $totalColunaDisponivel = array_fill(0, $numeroCargos + 2, 0);

        $totalColunaOcupado = array();
        $totalColunaOcupado = array_fill(0, $numeroCargos + 2, 0);

        # Cria e preenche o array do label
        $label = array("Centro");
        foreach ($cargos as $cc) {
            $label[] = $pessoal->get_nomeCargo($cc);
        }
        $label[] = "Total";

        # Zera o contador de linha
        $linha = 0;

        # Percorre as diretorias
        foreach ($diretorias as $dd) {

            $resultado[$linha][0] = $dd;    // Sigla da Diretoria 
            $coluna = 1;                    // Inicia a coluna
            # Zera os totais de linha
            $totalLinha = 0;                // Zera totalizador de cada linha
            $totalLinhaDisponivel = 0;                // Zera totalizador de cada linha
            $totalLinhaOcupado = 0;                // Zera totalizador de cada linha
            # Percorre as colunas / Cargos
            foreach ($cargos as $cc) {
                # Faz os cálculos
                $quantidade = $this->get_numVagasCargoDiretoria($cc, $dd);
                $disponivel = $this->get_numVagasCargoDiretoriaDisponiveis($cc, $dd);
                $ocupado = $this->get_numVagasCargoDiretoriaOcupados($cc, $dd);

                # Joga os Valores no array
                #$resultado[$linha][$coluna] = "$quantidade ( <span id='verde'>$disponivel</span> / <span id='vermelho'>$ocupado</span> )";

                switch ($tipo) {
                    case "o":
                        $resultado[$linha][$coluna] = $ocupado;
                        break;
                    case "d" :
                        $resultado[$linha][$coluna] = $disponivel;
                        break;
                    default :
                        $resultado[$linha][$coluna] = $quantidade;
                        break;
                }

                # Somatorio da linha
                $totalLinha += $quantidade;
                $totalLinhaDisponivel += $disponivel;
                $totalLinhaOcupado += $ocupado;

                # Somatório da coluna
                $totalColuna[$coluna] += $quantidade;
                $totalColunaDisponivel[$coluna] += $disponivel;
                $totalColunaOcupado[$coluna] += $ocupado;

                # Incrementa a coluna
                $coluna++;
            }

            # Faz a última coluna com o total da linha
            #$resultado[$linha][$coluna] = "$totalLinha ( <span id='verde'>$totalLinhaDisponivel</span> / <span id='vermelho'>$totalLinhaOcupado</span> )";

            switch ($tipo) {
                case "o":
                    $resultado[$linha][$coluna] = $totalLinhaOcupado;
                    break;
                case "d" :
                    $resultado[$linha][$coluna] = $totalLinhaDisponivel;
                    break;
                default :
                    $resultado[$linha][$coluna] = $totalLinha;
                    break;
            }


            # Somatório da coluna
            $totalColuna[$coluna] += $totalLinha;
            $totalColunaDisponivel[$coluna] += $totalLinhaDisponivel;
            $totalColunaOcupado[$coluna] += $totalLinhaOcupado;

            # Incrementa a linha
            $linha++;


            # Faz a última lina com os totais das colunas
            $resultado[$linha][0] = "Total";
            $coluna = 1;

            # Percorre as colunas
            foreach ($cargos as $cc) {
                #$resultado[$linha][$coluna] = "$totalColuna[$coluna] ( <span id='verde'>$totalColunaDisponivel[$coluna]</span> / <span id='vermelho'>$totalColunaOcupado[$coluna]</span> )";

                switch ($tipo) {
                    case "o":
                        $resultado[$linha][$coluna] = $totalColunaOcupado[$coluna];
                        break;
                    case "d" :
                        $resultado[$linha][$coluna] = $totalColunaDisponivel[$coluna];
                        break;
                    default :
                        $resultado[$linha][$coluna] = $totalColuna[$coluna];
                        break;
                }
                $coluna++;
            }
        }
        #$resultado[$linha][$coluna] = "$totalColuna[$coluna] ( <span id='verde'>$totalColunaDisponivel[$coluna]</span> / <span id='vermelho'>$totalColunaOcupado[$coluna]</span> )";

        switch ($tipo) {
            case "o":
                $resultado[$linha][$coluna] = $totalColunaOcupado[$coluna];
                $titulo = "Vagas Ocupadas";
                break;
            case "d" :
                $resultado[$linha][$coluna] = $totalColunaDisponivel[$coluna];
                $titulo = "Vagas Disponíveis";
                break;
            default :
                $resultado[$linha][$coluna] = $totalColuna[$coluna];
                $titulo = "Vagas Totais";
                break;
        }

        $tabela = new Tabela();
        $tabela->set_titulo($titulo);
        $tabela->set_conteudo($resultado);
        $tabela->set_label($label);
        $tabela->set_width(array(25, 25, 25, 25));
        $tabela->set_totalRegistro(false);
        $tabela->set_align(array("left", "center"));
        $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                'valor' => "Total",
                'operador' => '=',
                'id' => 'totalVagas')));
        $tabela->show();
    }

    ###########################################################

    /**
     * Método get_idConcursoProfessor
     * fornece o idConcurso de um professor
     * 
     * @param	string $idServidor O $idServidor do servidor Professor
     */
    function get_idConcursoProfessor($idServidor) {

        # Conecta o banco
        $pessoal = new Pessoal();

        $select = 'SELECT idConcurso
                     FROM tbvagahistorico
                    WHERE idServidor = ' . $idServidor;

        $dado = $pessoal->select($select, false);

        if (empty($dado[0])) {
            return null;
        } else {
            return $dado[0];
        }
    }

    ###########################################################

    /**
     * Método get_idConcursoProfessor
     * fornece o idConcurso de um professor
     * 
     * @param	string $idServidor O $idServidor do servidor Professor
     */
    function exibeGrafico($idvaga) {

        # Conecta o banco
        $pessoal = new Pessoal();

        # Pega todos os concursos dessa vaga
        $select = 'SELECT *
                     FROM tbvagahistorico
                    WHERE idVaga = ' . $idvaga;

        $dado = $pessoal->select($select);

        # Classes
        $concurso = new Concurso();

        # Inicia os arrays da tabela
        $label = array();
        $conteudo = array();


        # Percorre a tabela
        foreach ($dados as $dd) {

            $label[] = $concurso->get_nomeConcurso($dd["idConcurso"]);
            $laboratorio[] = $pessoal->get_nomeLotacao($dd["idiLotacao"]);
            $area[] = $dd["area"];

            # Parei aqui
        }

        $tabela = new Tabela();
        $tabela->set_titulo("Tabela Simples");
        $tabela->set_conteudo($laboratorio);
        $tabela->set_label($label);
        $tabela->set_align(array("center"));
        $tabela->show();
    }

    ###########################################################

    /**
     * Método get_laboratorioOrigem
     * fornece o primeiro laboratório de uma vaga. o laborató ao qual a vaga foi criada,
     * 
     * @param	integer $idVaga O id da vaga
     */
    function get_laboratorioOrigem($idVaga) {

        # Conecta o banco
        $pessoal = new Pessoal();

        $select = "SELECT idLotacao
                     FROM tbvagahistorico JOIN tbconcurso USING (idConcurso) 
                    WHERE idVaga = $idVaga
                 ORDER BY tbconcurso.dtPublicacaoEdital LIMIT 1";

        $dado = $pessoal->select($select, false);

        return $dado[0];
    }

    ###########################################################

    /**
     * Método get_laboratorioOrigem
     * fornece o primeiro laboratório de uma vaga. o laborató ao qual a vaga foi criada,
     * 
     * @param	integer $idVaga O id da vaga
     */
    function exibeLaboratorioOrigem($idVaga) {

        # Conecta com o banco de dados
        $pessoal = new Pessoal();

        return $pessoal->get_nomeLotacao3($this->get_laboratorioOrigem($idVaga));
    }

    ###########################################################

    /**
     * Método get_nomeLaboratorioOrigem
     * fornece o primeiro laboratório de uma vaga. o laborató ao qual a vaga foi criada,
     * 
     * @param	integer $idVaga O id da vaga
     */
    function get_nomeLaboratorioOrigem($idVaga) {

        # Conecta o banco
        $pessoal = new Pessoal();

        # Pega o idLotação
        $idLotacao = $this->get_laboratorioOrigem($idVaga);


        # Pega o nome dessa lotação
        if (vazio($idLotacao)) {
            $nome = null;
        } else {
            $nome = $pessoal->get_lotacaoGerencia($idLotacao);
        }

        # Retorna o nome
        return $nome;
    }

    ###########################################################

    /**
     * Método verificaProblemaVaga
     * Verifica se tem algum problema na vaga
     * 
     * @param	string $idVaga O id da vaga do servidor
     */
    function verificaProblemaVaga($idVaga) {

        # Inicia as variáveis
        $erro = null;

        if (vazio($idVaga)) {
            alert("Tem que informar o idVaga");
        } else {
            # Conecta o banco
            $pessoal = new Pessoal();
            $concurso = new Concurso();

            # Pega o id da lotação de origem
            $idOrigem = $this->get_laboratorioOrigem($idVaga);

            ############################
            # Problema: Verifica se tem algum concurso para laboratório diferente do laboratório de origem

            if (!vazio($idOrigem)) {
                # Percorre o registros dessa vaga
                $select = "SELECT idLotacao
                             FROM tbvagahistorico
                            WHERE idVaga = $idVaga
                              AND idLotacao <> $idOrigem";

                $num = $pessoal->count($select);

                if ($num <> 0) {
                    $erro[] = 'Existem concursos para essa vaga com laboratório diferente do laboratório de origem';
                }
            } else {
                # Problema: quando não tem laboratório de origem

                $erro[] = 'Não existe concurso cadastrado para essa vaga e assim sendo não é possível descobrir o laboratório de origem.';
            }

            ############################
            # Problema: Verifica se tem algum ocupante para laboratório diferente do laboratório de origem
            #              Esse problema é relativo, pois a lotação do servidor pode variar e dar um falso problema
            # Pega os servidores dessa vaga
            $select = "SELECT idServidor
                         FROM tbvagahistorico
                        WHERE idVaga = $idVaga";

            $relacao = $pessoal->select($select);

            # Percorre comparando a lotacção de cada um com a lotação de origem
            foreach ($relacao as $rr) {

                # Pega o idLotação desse servidor
                if (!vazio($rr[0])) {
                    $idlotacao = $pessoal->get_idLotacao($rr[0]);
                }

                if ((!vazio($rr[0])) AND ($idlotacao <> $idOrigem)) {
                    $erro[] = 'Existem servidores que ocupam ou ocuparam essa vaga que não estão lotados no laboratório de origem';
                    break;
                }
            }

            ############################
            # Problema: Verifica se o mesmo concurso aparece mais de uma vez
            # Percorre o registros dessa vaga
            $select = "SELECT idConcurso, COUNT(idConcurso) 
                         FROM tbvagahistorico
                        WHERE idVaga = $idVaga
                     GROUP BY idConcurso
                       HAVING COUNT(*) > 1";

            $num = $pessoal->count($select);

            if ($num <> 0) {
                $erro[] = 'O mesmo concurso aparece mais de uma vez nessa vaga';
            }

            ############################
            # Problema: Verifica se tem algum ocupante do cargo com data de admissão anterior ao concurso
            # Percorre o registros dessa vaga
            $select = "SELECT idConcurso, idServidor 
                         FROM tbvagahistorico
                        WHERE idVaga = $idVaga";

            $dados = $pessoal->select($select);

            foreach ($dados as $rr) {

                $dataAdmissao = null;
                $dataPublicacao = null;

                # Pega a data do concurso
                if (!vazio($rr[0])) {
                    $dadosConcurso = $concurso->get_dados($rr[0]);
                    $dataPublicacao = date_to_php($dadosConcurso["dtPublicacaoEdital"]);

                    # Pega a data de admissão do servidor
                    if (!vazio($rr[1])) {
                        $dataAdmissao = $pessoal->get_dtAdmissao($rr[1]);

                        if (dataMaior($dataAdmissao, $dataPublicacao) == $dataPublicacao) {
                            $erro[] = 'O Ocupante do cargo foi admitido antes do concurso';
                        }
                    }
                }
            }
        }

        return $erro;
    }

    ###########################################################

    /**
     * Método temProblema
     * Verifica se tem algum problema na vaga e exibe um sinal quando tem
     * 
     * @note Problemas procurados: laboratório de origem diferente do concurso ou do ocupante do cargo e 2 lançamento do mesmo concurso
     * 
     * @param	string $idVaga O id da vaga do servidor
     */
    function temProblema($idVaga) {

        # Verifica se tem problema
        $temProblema = $this->verificaProblemaVaga($idVaga);

        # Exibe ou não o aviso
        if (is_array($temProblema)) {
            $botao = new BotaoGrafico();
            $botao->set_imagem(PASTA_FIGURAS . 'aviso.png', 25, 25);
            $botao->set_title('Problemas Encontrados !!');
            $botao->show();
        }
    }

    ###########################################################

    /**
     * Método exibeDashboard
     * Exibe uma tela de resumo geral das vagas
     * 
     * @note Usado na área de Vagas quando não se informa o centro
     */
    function exibeDashboard() {

        $painel = new Callout();
        $painel->abre();

        tituloTable("Vagas de Docentes");
        br();

        #tituloTable("Vagas Totais");

        $grid2 = new Grid();
        $grid2->abreColuna(5);
        br(2);

        $numVagas = $this->get_numVagasDiretoria();
        $numVagasDisponiveis = $this->get_numVagasCargoDiretoriaDisponiveis();
        $numVagasOcupadas = $this->get_numVagasCargoDiretoriaOcupados();
        $arr = array(array("Disponíveis", $numVagasDisponiveis),
            array("Ocupadas", $numVagasOcupadas));

        p($numVagas, "estatisticaNumero");
        br(2);

        $grid3 = new Grid();
        $grid3->abreColuna(6);

        $painel = new Callout("primary");
        $painel->abre();

        p("$numVagasDisponiveis Disponíveis", "estatisticaTexto");

        $painel->fecha();

        $grid3->fechaColuna();
        $grid3->abreColuna(6);

        $painel = new Callout("warning");
        $painel->abre();

        p("$numVagasOcupadas Ocupadas", "estatisticaTexto");

        $painel->fecha();

        $grid3->fechaColuna();
        $grid3->fechaGrid();

        $grid2->fechaColuna();
        $grid2->abreColuna(7);

        #$painel = new Callout();
        #$painel->abre();    
        # Chart
        #tituloTable($item[0]);
        $chart = new Chart("Pie", $arr);
        $chart->set_idDiv('vagas');
        #$chart->set_legend(false);
        $chart->set_pieHole(true);
        $chart->set_tamanho(500, 300);
        $chart->show();

        #$painel->fecha(); 

        $grid2->fechaColuna();
        $grid2->abreColuna(6);

        $this->exibeVagasDisponiveis();

        $grid2->fechaColuna();
        $grid2->abreColuna(6);

        $this->exibeVagasOcupadas();

        $grid2->fechaColuna();
        $grid2->fechaGrid();

        $painel->fecha();

//        $painel = new Callout();
//        $painel->abre();
//
//        tituloTable("Vagas Por Centro");
//        br();
//
//        $grid2 = new Grid();
//
//
//        # Centros Possíveis
//        $centros = array("CCT", "CCTA", "CCH", "CBB");
//
//        foreach ($centros as $cc) {
//
//            $grid2->abreColuna(6);
//
//            tituloTable($cc);
//
//            $titularDisp = $this->get_numVagasCargoDiretoriaDisponiveis(129, $cc);
//            $titularOcup = $this->get_numVagasCargoDiretoriaOcupados(129, $cc);
//            $titularTotal = $titularDisp + $titularOcup;
//
//            $associaDisp = $this->get_numVagasCargoDiretoriaDisponiveis(128, $cc);
//            $associaOcup = $this->get_numVagasCargoDiretoriaOcupados(128, $cc);
//            $associaTotal = $associaDisp + $associaOcup;
//
//            $diponiTotal = $associaDisp + $titularDisp;
//            $ocupadoTotal = $associaOcup + $titularOcup;
//
//            $arrayResult = array(array("Professor Titular", $titularDisp, $titularOcup, $titularTotal),
//                array("Professor Associado", $associaDisp, $associaOcup, $associaTotal),
//                array("Total", $diponiTotal, $ocupadoTotal, $ocupadoTotal + $diponiTotal));
//
//            $arrayTitular = array(array("Ocupada", $titularOcup),
//                array("Disponível", $titularDisp));
//
//            $arrayAssociado = array(array("Ocupada", $associaOcup),
//                array("Disponível", $associaDisp));
//
//            # Chart
//            $chart = new Chart("Pie", $arrayTitular);
//            $chart->set_idDiv("vagasO$cc");
//            $chart->set_title("Titular");
//            $chart->set_tresd(true);
//            $chart->set_cores(array('#1B4F72', '#2980B9'));
//            #$chart->set_legend(array("VagasT","tt"));
//            #$chart->set_pieHole(true);
//            $chart->set_tamanho(400, 300);
//            #$chart->show();
//            # Chart
//            $chart = new Chart("Pie", $arrayAssociado);
//            $chart->set_idDiv("vagasD$cc");
//            $chart->set_title("Associado");
//            $chart->set_tresd(true);
//            $chart->set_cores(array('#e0440e', '#e6693e'));
//            #$chart->set_pieHole(true);
//            $chart->set_tamanho(400, 300);
//            #$chart->show();
//            # Tabela
//            $tabela = new Tabela();
//            $tabela->set_conteudo($arrayResult);
//            #$tabela->set_titulo($cc);
//            $tabela->set_label(array("Cargo", "Disponíveis", "Ocupadas", "Total"));
//            $tabela->set_width(array(40, 20, 20, 20));
//            $tabela->set_align(array("left"));
//            $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
//                    'valor' => "Total",
//                    'operador' => '=',
//                    'id' => 'estatisticaTotal')));
//            $tabela->set_totalRegistro(false);
//            $tabela->show();
//
//            $grid2->fechaColuna();
//        }
//
//        $grid2->fechaGrid();
//
//        $painel->fecha();
    }

    ###########################################################

    /**
     * Método temProblema
     * Verifica se tem algum problema na vaga e exibe um sinal quando tem
     * 
     * @note Problemas procurados: laboratório de origem diferente do concurso ou do ocupante do cargo e 2 lançamento do mesmo concurso
     * 
     * @param	string $idVaga O id da vaga do servidor
     */
    function menu($parametroCentro) {

        $painel = new Callout();
        $painel->abre();

        # Centros Possíveis
        $centros = array("Todos", "CCT", "CCTA", "CCH", "CBB");

        # Inicia o Menu de Cargos                
        $menu = new Menu("menuProcedimentos");
        $menu->add_item('titulo', 'Lotação (Centro)');

        foreach ($centros as $cc) {

            if ($cc == "Todos") {
                $numVagas = $this->get_numVagasDiretoria();
            } else {
                $numVagas = $this->get_numVagasDiretoria($cc);
            }

            if ($parametroCentro == $cc) {
                $menu->add_item('link', "<b>$cc ($numVagas)</b>", '?parametroCentro=' . $cc);
            } elseif ((vazio($parametroCentro)) AND ($cc == "Todos")) {
                $menu->add_item('link', '<b>Todos</b>', '?parametroCentro=' . $cc);
            } else {
                $menu->add_item('link', "$cc ($numVagas)", '?parametroCentro=' . $cc);
            }
        }

        if (!vazio($parametroCentro)) {
            $menu->add_item('titulo', 'Relatórios');
            $menu->add_item('linkWindow', "Vagas por Laboratório ($parametroCentro)", "../grhRelatorios/vagas.porLaboratorio.php?parametroCentro=" . $parametroCentro);
        } else {
            $menu->add_item('titulo', 'Relatórios');
            $menu->add_item('linkWindow', 'Docentes Ativos com Vaga', '../grhRelatorios/vagas.professores.ativos.php');
            $menu->add_item('linkWindow', 'Docentes Inativos com Vaga', '../grhRelatorios/vagas.professores.inativos.php');
            $menu->add_item('linkWindow', 'Vagas Geral', '../grhRelatorios/vagas.geral.php');
            $menu->add_item('linkWindow', 'Vagas Resumo', '../grhRelatorios/vagas.resumo.php');
            $menu->add_item('linkWindow', 'Vagas Resumo Por Lotação', '../grhRelatorios/vagas.resumo.lotacao.php');
        }

        $menu->show();

        $painel->fecha();
    }
    
    ###########################################################

    function get_idVaga($idServidor) {

        if (is_numeric($idServidor)) {

            # Pega os dados
            $pessoal = new Pessoal();

            $select = "SELECT idVaga
                         FROM tbvagahistorico
                        WHERE idServidor = {$idServidor}";

            $dados = $pessoal->select($select, false);

            return $dados[0];
        } else {
            return null;
        }
    }
    
    ###########################################################
}
