<?php

class Checkup {

    /**
     * Classe Checup
     * 
     * Faz um checup no banco de dados pessoal a procura de erros quanto ao banco de dados quanto as regras de negócio
     * 
     * By Alat
     */
    private $lista = false;       // Informa se será listagem ou somente contagem dos registros
    private $linkEditar = 'servidor.php?fase=editar&id=';

    ##################################################

    public function set_lista($lista) {
        if (!empty($lista)) {
            $this->lista = $lista;
        }
    }

    ##################################################

    /**
     * Método listaCategoria
     * 
     * Lista alertas por categoria
     */
    public function listaCategoria($categoria) {

        # Pega todos as informações da classe
        $api = new ReflectionClass($this);

        # Inicia um array para guardar o retorno 
        $metodoRetorno = array();

        # Percorre todos os métodos da classe e guarda no array seu retorno
        foreach ($api->getMethods() as $method) {
            # Retira os métodps que não são de checkup
            if (($method->getName() <> 'listaCategoria')
                    AND ($method->getName() <> 'listaPorServidor')
                    AND ($method->getName() <> '__construct')
                    AND ($method->getName() <> 'set_linkEditar')) {

                # Joga para $metodo o nome do método
                $metodo = $method->getName();

                $retorno = $this->$metodo(null, $categoria);

                if (!empty($retorno)) {
                    $metodoRetorno[] = $retorno;
                }
            }
        }

        # Percorre o array $metodoRetorno e exibe a lista
        if (empty($metodoRetorno)) {
            br();
            p("Não há alertas para esta categoria.", "f14", "center");
        } else {
            echo "<ul class='checkupResumo'>";
            foreach ($metodoRetorno as $listaRetorno) {

                $link = new Link($listaRetorno[0], "?fase=tabela&alerta=" . $listaRetorno[1]);
                $link->set_id("checkupResumo");
                echo "<li id='checkupResumo'>";
                $link->show();
                echo "</li>";
            }
            echo "</ul>";
        }
    }

    ##################################################

    /**
     * Método listaPorServidor
     * 
     * Lista os alertas por servidor
     */
    public function listaPorServidor($idservidor) {

        # Pega todos as informações da classe
        $api = new ReflectionClass($this);

        # Inicia um array para guardar o retorno 
        $metodoRetorno = array();

        # Percorre todos os métodos da classe e guarda no array seu retorno
        foreach ($api->getMethods() as $method) {
            # Retira os métodps que não são de checkup
            if (($method->getName() <> 'listaCategoria')
                    AND ($method->getName() <> 'listaPorServidor')
                    AND ($method->getName() <> '__construct')
                    AND ($method->getName() <> 'set_linkEditar')) {

                # Joga para $metodo o nome do método
                $metodo = $method->getName();
                $retorno = $this->$metodo($idservidor);

                if (!empty($retorno)) {
                    $metodoRetorno[] = $retorno;
                }
            }
        }

        return $metodoRetorno;
    }

    ##################################################

    /**
     * Método get_licencaVencendo
     * 
     * Servidores com Licença vencendo este ano
     */
    public function get_licencaVencendo($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "licencas" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT tbservidor.idFuncional,
                          tbpessoa.nome,
                        tbperfil.nome,
                        tbtipolicenca.nome,
                        tblicenca.dtInicial,
                        tblicenca.numDias,
                        ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1),
                        tbservidor.idServidor
                   FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                   LEFT JOIN tblicenca USING (idServidor)
                                   LEFT JOIN tbtipolicenca ON (tblicenca.idTpLicenca = tbtipolicenca.idTpLicenca)
                                   LEFT JOIN tbperfil USING (idPerfil)
                  WHERE tbservidor.situacao = 1
                    AND YEAR(ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)) = "' . date('Y') . '"';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY 7';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) com licença terminando em ' . date('Y');

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_titulo($titulo);
            $tabela->set_label(['IdFuncional', 'Servidor', 'Perfil', 'Licença', 'Data Inicial', 'Dias', 'Data Final']);
            $tabela->set_align(['center', 'left', 'center', 'left']);
            #$tabela->set_classe([null, "Pessoal"]);
            #$tabela->set_metodo([null, "get_nomeECargo"]);
            $tabela->set_funcao([null, null, null, null, "date_to_php", null, "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_trienioVencendo
     * 
     * Servidores com trênio vencendo este ano
     */
    public function get_trienioVencendo($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "trienio" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = '(SELECT DISTINCT tbservidor.idFuncional,
                  tbpessoa.nome,
                  tbservidor.dtadmissao,
                  CONCAT(MAX(tbtrienio.percentual),"%"),
                  MAX(tbtrienio.dtInicial),
                  DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR),
                  tbservidor.idServidor
             FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                             LEFT JOIN tbtrienio USING (idServidor)
            WHERE tbservidor.situacao = 1
              AND idPerfil = 1';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' 
         GROUP BY tbservidor.idServidor
         HAVING YEAR (DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR)) = ' . date('Y') . '
         ORDER BY 6)
         UNION
         (SELECT DISTINCT tbservidor.idFuncional,  
                  tbpessoa.nome,
                  tbservidor.dtadmissao,
                  "",
                  "",
                  DATE_ADD(tbservidor.dtadmissao, INTERVAL 3 YEAR),
                  tbservidor.idServidor
             FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                             LEFT JOIN tbtrienio USING (idServidor)
            WHERE tbservidor.situacao = 1
              AND idPerfil = 1';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= '        
         GROUP BY tbservidor.idServidor
         HAVING YEAR (DATE_ADD(tbservidor.dtadmissao, INTERVAL 3 YEAR)) = ' . date('Y') . '
             AND MAX(tbtrienio.dtInicial) IS null
         ORDER BY 6)
         ORDER BY 6';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) estatutários com triênio vencendo em ' . date('Y');

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Nome', 'Admissão', 'Último Percentual', 'Último Triênio', 'Próximo Triênio']);
            $tabela->set_align(['center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_funcao([null, null, "date_to_php", null, "date_to_php", "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_trienioVencido
     * 
     * Servidores com trênio vencido anterior a esse ano
     */
    public function get_trienioVencido($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "trienio" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = '(SELECT DISTINCT tbservidor.idFuncional,  
                  tbpessoa.nome,
                  tbservidor.dtadmissao,
                  CONCAT(MAX(tbtrienio.percentual),"%"),
                  MAX(tbtrienio.dtInicial),
                  DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR),
                  tbservidor.idServidor
             FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                             LEFT JOIN tbtrienio USING (idServidor)
            WHERE tbservidor.situacao = 1
              AND idPerfil = 1';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= '        
         GROUP BY tbservidor.idServidor
         HAVING YEAR (DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR)) < ' . date('Y') . '
         ORDER BY 6)
         UNION
         (SELECT DISTINCT tbservidor.idFuncional,  
                  tbpessoa.nome,
                  tbservidor.dtadmissao,
                  "",
                  "",
                  DATE_ADD(tbservidor.dtadmissao, INTERVAL 3 YEAR),
                  tbservidor.idServidor
             FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                             LEFT JOIN tbtrienio USING (idServidor)
            WHERE tbservidor.situacao = 1
              AND idPerfil = 1';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= '                      
         GROUP BY tbservidor.idServidor
         HAVING YEAR (DATE_ADD(tbservidor.dtadmissao, INTERVAL 3 YEAR)) < ' . date('Y') . '
             AND MAX(tbtrienio.dtInicial) IS null
         ORDER BY 6)
         ORDER BY 6';

            $result = $servidor->select($select);
            $count = $servidor->count($select);

            # Cabeçalho da tabela
            $label = ['IdFuncional', 'Nome', 'Admissão', 'Último Percentual', 'Último Triênio', 'Deveriam ter recebido em:'];
            $align = ['center', 'left'];
            $titulo = 'Servidor(es) estatutários com triênio vencido antes de ' . date('Y');
            $funcao = [null, null, "date_to_php", null, "date_to_php", "date_to_php"];

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label($label);
            $tabela->set_align($align);
            $tabela->set_titulo($titulo);
            $tabela->set_funcao($funcao);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_auxilioCrecheVencido
     * 
     * Servidores com o auxílio creche vencendo este ano
     */
    public function get_auxilioCrecheVencido($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "creche" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT tbservidor.idFuncional,
                  tbpessoa.nome,
                  tbdependente.nome,
                  tbdependente.dtNasc,
                  dtTermino,
                  ciExclusao,
                  processo,
                  tbservidor.idServidor
             FROM tbdependente JOIN tbpessoa USING (idpessoa)
                               JOIN tbservidor USING (idpessoa)
            WHERE tbservidor.situacao = 1
              AND idPerfil = 1
              AND YEAR(dtTermino) = "' . date('Y') . '"';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= '        
         ORDER BY dtTermino';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) estatutários com o auxílio creche vencendo em ' . date('Y');
            $label = ["IdFuncional", "Servidor", "Dependente", "Nascimento", "Término do Aux.", "CI Exclusão", "Processo"];

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(["IdFuncional", "Servidor", "Dependente", "Nascimento", "Término do Aux.", "CI Exclusão", "Processo"]);
            $tabela->set_align(['center', 'left', 'left', 'center', 'center', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_funcao([null, null, null, "date_to_php", "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_motoristaCarteiraVencida
     * 
     * Motoristas com carteira de habilitação vencida no sistema
     */
    public function get_motoristaCarteiraVencida($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "motorista" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT tbservidor.idFuncional, 
                          tbpessoa.nome,
                          motorista,
                          tbdocumentacao.dtVencMotorista,
                          tbservidor.idServidor,
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idpessoa)
                                     LEFT JOIN tbdocumentacao USING (idpessoa)
                    WHERE tbservidor.situacao = 1
                    AND tbservidor.idcargo = 63
                    AND tbdocumentacao.dtVencMotorista < now()';

            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }

            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Motorista(s) com carteira de habilitação vencida';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Nome', 'Habilitação', 'Data da Carteira', 'Cargo']);
            $tabela->set_align(['center', 'left', 'center', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_cargo"]);
            $tabela->set_funcao([null, null, null, "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Solicitar aos motoristas que compareçam a GRH com a cópia"
                                . " da carteira para ser arquivada.<br/>Lembre-se de cadastrar"
                                . " no sistema, na área de documentos do motorista, a nova data"
                                . " da carteira.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_motoristaSemDataCarteira
     * 
     * Motoristas com carteira de habilitação sem data de vencimento cadastrada no sistema
     */
    public function get_motoristaSemDataCarteira($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "motorista" OR!empty($idServidor)) {


            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT tbservidor.idFuncional, 
                          tbpessoa.nome,
                          motorista,
                          tbdocumentacao.dtVencMotorista,
                          tbservidor.idServidor,
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idpessoa)
                                     LEFT JOIN tbdocumentacao USING (idpessoa)
                    WHERE tbservidor.situacao = 1
                    AND tbservidor.idcargo = 63
                    AND tbdocumentacao.dtVencMotorista is null';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Motorista(s) com carteira de habilitação sem data de vencimento';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Nome', 'Habilitação', 'Data da Carteira', 'Cargo']);
            $tabela->set_align(['center', 'left', 'center', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_cargo"]);
            $tabela->set_funcao([null, null, null, "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Solicitar aos motoristas que compareçam a GRH com a cópia"
                                . " da carteira para ser arquivada.<br/>Lembre-se de cadastrar"
                                . " no sistema, na área de documentos do motorista, a nova data"
                                . " da carteira.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_motoristaSemCarteira
     * 
     * Motorista sem número da carteira de habilitação cadastrada:
     */
    public function get_motoristaSemCarteira($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "motorista" OR!empty($idServidor)) {


            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT tbservidor.idFuncional, 
                          tbservidor.matricula,  
                          tbpessoa.nome,
                          motorista,
                          tbservidor.idServidor,
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idpessoa)
                                     LEFT JOIN tbdocumentacao USING (idpessoa)
                    WHERE tbservidor.situacao = 1
                    AND tbservidor.idcargo = 63
                    AND (tbdocumentacao.motorista is null OR tbdocumentacao.motorista ="")';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Motorista(s) sem número da carteira de habilitação cadastrada:';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Habilitação', 'Cargo']);
            $tabela->set_align(['center', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_cargo"]);
            #$tabela->set_funcao($funcao);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Solicitar aos motoristas que compareçam a GRH com"
                                . " a cópia da carteira para ser arquivada. Lembre-se de"
                                . " cadastrar no sistema, na área de documentos do motorista,"
                                . " os dados da carteira de habilitação");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorCom74
     * 
     * Servidor estatutário que faz 75 anos este ano (Preparar aposentadoria compulsória)
     */
    public function get_servidorCom74($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "aposentadoria" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT tbservidor.idFuncional,  
                          tbpessoa.nome,
                          dtNasc,
                          TIMESTAMPDIFF(YEAR,tbpessoa.dtNasc,CURDATE()),
                          tbservidor.idServidor,
                          tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                    WHERE tbservidor.situacao = 1
                    AND YEAR(CURDATE()) - YEAR(tbpessoa.dtNasc) = 75
                    AND idPerfil = 1';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) estatutário(s) que faz 75 anos este ano. Preparar aposentadoria compulsória';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Nome', 'Data de Nascimento', 'Idade', 'Lotação', 'Cargo']);
            $tabela->set_align(['center', 'left', 'center', 'center', 'left', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_lotacao", "get_cargo"]);
            $tabela->set_funcao([null, null, "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Avisar ao servidor sobre a aposentadoria compulsória.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorComMais74
     * 
     * Servidor estatutário com 75 anos ou mais (Aposentar Compulsoriamente)
     */
    public function get_servidorComMais75($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "aposentadoria" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT tbservidor.idFuncional,  
                          tbpessoa.nome,
                          dtNasc,
                          TIMESTAMPDIFF(YEAR,tbpessoa.dtNasc,CURDATE()),
                          tbservidor.idServidor,
                          tbservidor.idServidor                          
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                    WHERE tbservidor.situacao = 1
                    AND TIMESTAMPDIFF(YEAR,tbpessoa.dtNasc,CURDATE()) >= 75 
                    AND idPerfil = 1';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) estatutário com 75 anos ou mais. Aposentar Compulsoriamente';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Nome', 'Data de Nascimento', 'Idade', 'Lotação', 'Cargo']);
            $tabela->set_align(['center', 'left', 'center', 'center', 'left', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_lotacao", "get_cargo"]);
            $tabela->set_funcao([null, null, "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Servidores com mais de 75 anos dever ser aposentados compulsoriamente !");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorComMaisde1MatriculaAtiva
     * 
     * Servidor estatutário com mais de uma matriculka ativa
     */
    public function get_servidorComMaisde1MatriculaAtiva($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "cadastro" OR!empty($idServidor)) {


            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor,
                          tbsituacao.situacao,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                                     LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao)
                    WHERE idPessoa IN 
                    (SELECT idpessoa 
                       FROM tbservidor 
                      WHERE tbservidor.situacao = 1 GROUP BY idPessoa HAVING COUNT(*) > 1 
                   ORDER BY idpessoa)
                      AND tbservidor.situacao = 1';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }

            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) com mais de um vínculo (matrícula) ativo';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Perfil', 'Lotação', 'Cargo', 'Situação']);
            $tabela->set_align(['center', 'center', 'left', 'center', 'left', 'left', 'center']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_lotacao", "get_cargo"]);
            #$tabela->set_funcao($funcao);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Servidor com mais de 1 matriculas Ativas !! "
                                . "Houve algum erro no sistema, favor verificar. "
                                . "Somente uma matrícula deveria estar ativa");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorComPerfilOutros
     * 
     * Servidor Ativo com perfil outros
     */
    public function get_servidorComPerfilOutros($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "aposentadoria" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor,
                          tbsituacao.situacao,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                                     LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao)
                    WHERE idPerfil = 8
                      AND tbservidor.situacao = 1';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) com perfil outros';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Perfil', 'Lotação', 'Cargo', 'Situação']);
            $tabela->set_align(['center', 'center', 'left', 'center', 'left', 'left', 'center']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_lotacao", "get_cargo"]);
            #$tabela->set_funcao($funcao);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("O perfil outros foi definido na importação para "
                                . "servidores que estavam com perfil em branco. "
                                . "Deve-se analisar para saber o real perfil desse servidor "
                                . "ou se não for servidor efetuar sua exclusão do sistema.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorSemPerfil
     * 
     * Servidor com perfil outros
     */
    public function get_servidorSemPerfil($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "perfil" OR!empty($idServidor)) {


            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor,
                          tbsituacao.situacao,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                                     LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao)
                    WHERE idPerfil is null
                      AND tbservidor.situacao = 1';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) sem perfil cadastrado';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Nome', 'Perfil', 'Lotação', 'Cargo', 'Situação']);
            $tabela->set_align(['center', 'left', 'center', 'left', 'left', 'center']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, "get_lotacao", "get_cargo"]);
            #$tabela->set_funcao($funcao);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Deve ter algum erro no sistema, favor verificar. "
                                . "Todos os servidores devem tem um perfil cadastrado.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorTecnicoEstatutarioSemConcurso
     * 
     * Servidor Concursado sem concurso cadastrado
     */
    public function get_servidorTecnicoEstatutarioInativosSemConcurso($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "concurso" OR!empty($idServidor)) {


            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          dtAdmissao,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor,
                          tbsituacao.situacao,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                                     LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao)
                    WHERE idConcurso is null
                      AND tbservidor.situacao <> 1
                      AND idPerfil = 1
                      AND (idCargo IS NULL OR (idCargo <> 128 AND idCargo <> 129))';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY dtAdmissao,tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) técnico(s) estatutário(s) inativos sem concurso cadastrado';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Admissão', 'Nome', 'Perfil', 'Lotação', 'Cargo', 'Situação']);
            $tabela->set_align(['center', 'center', 'center', 'left', 'center', 'left', 'left', 'center']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, null, "get_lotacao", "get_cargo"]);
            $tabela->set_funcao([null, "dv", "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Todo servidor concursado deve ter cadastrado o "
                                . "concurso no qual foi aprovado.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorTecnicoEstatutarioSemConcurso
     * 
     * Servidor Concursado sem concurso cadastrado
     */
    public function get_servidorTecnicoCeletistaInativosSemConcurso($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "concurso" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          dtAdmissao,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor,
                          tbsituacao.situacao,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                                     LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao)
                    WHERE idConcurso is null
                      AND tbservidor.situacao <> 1
                      AND idPerfil = 4
                      AND dtAdmissao > "1997-05-08"
                      AND (idCargo <> 128 AND idCargo <> 129)';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY dtAdmissao,tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) técnico(s) celetista(s) inativos sem concurso cadastrado com admissão após 08/05/1997';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Admissão', 'Nome', 'Perfil', 'Lotação', 'Cargo', 'Situação']);
            $tabela->set_align(['center', 'center', 'center', 'left', 'center', 'left', 'left', 'center']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, null, "get_lotacao", "get_cargo"]);
            $tabela->set_funcao([null, "dv", "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Os servidores admitidos após 08/05/1997 devem "
                                . "ser, em sua maioria, concursados, pois esta é"
                                . " a data do primeiro concurso para servidores "
                                . "administrativos e técnicos da Uenf.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorTecnicoEstatutarioSemConcurso
     * 
     * Servidor Concursado sem concurso cadastrado
     */
    public function get_servidorTecnicoAtivosEstatutarioSemConcurso($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "concurso" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          dtAdmissao,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor,
                          tbsituacao.situacao,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                                     LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao)
                    WHERE idConcurso is null
                      AND tbservidor.situacao = 1
                      AND idPerfil = 1
                      AND (idCargo <> 128 AND idCargo <> 129)';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY dtAdmissao,tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) técnico(s) estatutário(s) ativos sem concurso cadastrado';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Admissão', 'Nome', 'Perfil', 'Lotação', 'Cargo', 'Situação']);
            $tabela->set_align(['center', 'center', 'left', 'center', 'left', 'left', 'center']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_lotacao", "get_cargo"]);
            $tabela->set_funcao([null, "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Todo servidor concursado deve ter cadastrado o "
                                . "concurso no qual foi aprovado.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorAtivoComConcursoPosteriorAdmissao
     * 
     * Servidor Concursado com concurso posterior a admissão
     */
    public function get_servidorAtivoComConcursoPosteriorAdmissao($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "concurso" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          dtPublicacaoEdital,
                          dtAdmissao,                          
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor,
                          tbsituacao.situacao,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                                     LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao)
                                     LEFT JOIN tbconcurso USING (idConcurso)
                    WHERE tbservidor.situacao = 1
                      AND dtAdmissao < tbconcurso.dtPublicacaoEdital
                      AND idPerfil = 1';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY dtAdmissao,tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) Ativos Admitido Antes do Concurso';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Concurso', 'Admissão', 'Nome', 'Perfil', 'Lotação', 'Cargo', 'Situação']);
            $tabela->set_align(['center', 'center', 'center', 'left', 'center', 'left', 'left', 'center']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, null, "get_lotacao", "get_cargo"]);
            $tabela->set_funcao([null, "date_to_php", "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Um servidor concursado não pode ser admitido "
                                . "antes de efetivamente passar no concurso.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorInativoComConcursoPosteriorAdmissao
     * 
     * Servidor Concursado com concurso posterior a admissão
     */
    public function get_servidorInativoComConcursoPosteriorAdmissao($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "concurso" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          dtPublicacaoEdital,
                          dtAdmissao,                          
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor,
                          tbsituacao.situacao,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                                     LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao)
                                     LEFT JOIN tbconcurso USING (idConcurso)
                    WHERE tbservidor.situacao <> 1
                      AND dtAdmissao < tbconcurso.dtPublicacaoEdital
                      AND idPerfil = 1';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY dtAdmissao,tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) Inativo Admitido Antes do Concurso';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Concurso', 'Admissão', 'Nome', 'Perfil', 'Lotação', 'Cargo', 'Situação']);
            $tabela->set_align(['center', 'center', 'center', 'left', 'center', 'left', 'left', 'center']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, null, "get_lotacao", "get_cargo"]);
            $tabela->set_funcao([null, "date_to_php", "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Um servidor concursado não pode ser admitido "
                                . "antes de efetivamente passar no concurso.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##############################################################

    /**
     * Método get_servidorProfessorAtivoSemConcurso
     * 
     * Servidor Concursado sem concurso cadastrado
     */
    public function get_servidorProfessorAtivoSemConcurso($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "concurso" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          dtAdmissao,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbsituacao.situacao,
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                                     LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao)
                                     LEFT JOIN tbvagahistorico ON (tbvagahistorico.idServidor = tbservidor.idServidor)
                    WHERE tbvagahistorico.idConcurso is null
                      AND tbservidor.situacao = 1
                      AND (idPerfil = 1 OR idPerfil = 4)
                      AND (idCargo = 128 OR idCargo = 129)';
            if (!empty($idServidor)) {
                $select .= ' AND tbservidor.idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY dtAdmissao,tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Professores ativos sem concurso cadastrado';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Admissão', 'Nome', 'Perfil', 'Lotação', 'Cargo', 'Situação']);
            $tabela->set_align(['center', 'center', 'center', 'left', 'center', 'left', 'left', 'center']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, null, "get_lotacao", "get_cargo"]);
            $tabela->set_funcao([null, "dv", "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Todo servidor concursado deve ter cadastrado o "
                                . "concurso no qual foi aprovado.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorProfessorAtivoSemConcurso
     * 
     * Servidor Concursado sem concurso cadastrado
     */
    public function get_servidorProfessorInativoSemConcurso($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "concurso" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          dtAdmissao,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbsituacao.situacao,
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                                     LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao)
                                     LEFT JOIN tbvagahistorico ON (tbvagahistorico.idServidor = tbservidor.idServidor)
                    WHERE tbvagahistorico.idConcurso is null
                      AND tbservidor.situacao <> 1
                      AND (idPerfil = 1 OR idPerfil = 4)
                      AND (idCargo = 128 OR idCargo = 129)';
            if (!empty($idServidor)) {
                $select .= ' AND tbservidor.idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY dtAdmissao,tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Professores inativos sem concurso cadastrado';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Admissão', 'Nome', 'Perfil', 'Lotação', 'Cargo', 'Situação']);
            $tabela->set_align(['center', 'center', 'center', 'left', 'center', 'left', 'left', 'center']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, null, "get_lotacao", "get_cargo"]);
            $tabela->set_funcao([null, "dv", "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Todo servidor concursado deve ter cadastrado o "
                                . "concurso no qual foi aprovado.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_cargoComissaoNomeacaoIgualExoneracao
     * 
     * Cargo em comissão nomeado e exonerado no mesmo dia?!
     */
    public function get_cargoComissaoNomeacaoIgualExoneracao($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "comissao" OR!empty($idServidor)) {


            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT distinct tbservidor.idFuncional,
                        tbservidor.matricula,
                        tbpessoa.nome,
                        tbcomissao.dtNom,
                        tbcomissao.dtExo,
                        idComissao,
                        concat(tbtipocomissao.simbolo," - ",tbtipocomissao.descricao),
                        idComissao,
                        tbservidor.idServidor
                   FROM tbservidor LEFT JOIN tbpessoa USING (idpessoa)
                                   LEFT JOIN tbcomissao USING (idServidor)
                                        JOIN tbtipocomissao USING (idTipoComissao)
                   WHERE tbtipocomissao.ativo AND (tbcomissao.dtNom = tbcomissao.dtExo)';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Cargo em comissão nomeado e exonerado no mesmo dia';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Nomeação', 'Exoneração', 'Descrição']);
            $tabela->set_align(['center', 'center', 'left', 'center', 'center', 'left']);
            $tabela->set_titulo($titulo);
            #$tabela->set_classe($classe);
            #$tabela->set_metodo($rotina);
            $tabela->set_funcao([null, "dv", null, "date_to_php", "date_to_php", "descricaoComissao"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Cargo em comissão nomeado e exonerado no mesmo dia.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorSemIdFuncional
     * 
     * Exibe servidor ativo sem id Funcional cadastrado que não for bolsista
     */
    public function get_servidorSemIdFuncional($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "cadastro" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idFuncional,
                          matricula,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor,
                          tbsituacao.situacao,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                                     LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao)
                    WHERE (idFuncional IS null OR idFuncional = "")
                      AND idPerfil <> 10
                      AND tbservidor.situacao = 1';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) sem id funcional cadastrado no sistema';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Perfil', 'Lotação', 'Cargo', 'Situação']);
            $tabela->set_align(['center', 'center', 'left', 'center', 'left', 'left', 'center']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_lotacao", "get_cargo"]);
            #$tabela->set_funcao($funcao);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorSemDtNasc
     * 
     * Servidor sem data de nasciment cadastrada
     */
    public function get_servidorSemDtNasc($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "cadastro" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT tbservidor.idFuncional,  
                          tbpessoa.nome,
                          dtNasc,
                          tbservidor.idServidor,
                          tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                    WHERE tbpessoa.dtNasc is null
                    AND situacao = 1';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) sem data de nascimento cadastrada no sistema';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Nome', 'Data de Nascimento', 'Lotação', 'Cargo']);
            $tabela->set_align(['center', 'left', 'center', 'left', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, "get_lotacao", "get_cargo"]);
            #$tabela->set_funcao($funcao);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("O cadastro da data de nascimento do servidor é "
                                . "necessário para diversas rotinas do sistema. "
                                . "Verifique se na pasta do arquivo não tem nenhuma"
                                . " cópia de documento que tenha essa informação.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorCedidoLotacaoErrada
     * 
     * Servidor DA UENF cedido a outro orgão que não está lotado na reitoria cedidos
     */
    public function get_servidorCedidoLotacaoErrada($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "cedidos" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT tbservidor.idFuncional,  
                          tbpessoa.nome,
                          tbhistcessao.orgao,
                          tbhistcessao.dtInicio,
                          tbhistcessao.dtFim,
                          tbservidor.idServidor,
                          tbservidor.idServidor
                    FROM tbhistcessao LEFT JOIN tbservidor USING (idServidor)
                                           JOIN tbpessoa USING (idPessoa)
                                           JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                           JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                    WHERE current_date() >= tbhistcessao.dtInicio
                      AND (isnull(tbhistcessao.dtFim) OR current_date() <= tbhistcessao.dtFim)
                      AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                      AND situacao = 1
                      AND tbhistlot.lotacao <> 113';
            if (!empty($idServidor)) {
                $select .= ' AND tbservidor.idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) cedido(s) pela UENF sem estar lotado no Reitoria - Cedidos';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Nome', 'Órgão', 'Início', 'Término', 'Lotação']);
            $tabela->set_align(['center', 'left', 'left', 'center', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, null, "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, null, "get_lotacao"]);
            $tabela->set_funcao([null, null, null, "date_to_php", "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("O servidor cedido pela UENF deve estar cadastrado"
                                . " no setor Reitoria - Cedidos");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorCedidoDataExpirada
     * 
     * Servidor DA UENF cedido a outro orgão onde a dta de término de cassão já passou mas continua cedido na reitoria cedidos
     */
    public function get_servidorCedidoDataExpirada($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "cedidos" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT tbservidor.idFuncional,  
                          tbpessoa.nome,
                          tbhistcessao.orgao,
                          tbhistcessao.dtInicio,
                          tbhistcessao.dtFim,
                          tbservidor.idServidor,
                          tbservidor.idServidor
                    FROM tbhistcessao LEFT JOIN tbservidor USING (idServidor)
                                           JOIN tbpessoa USING (idPessoa)
                                           JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                           JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                    WHERE current_date() > tbhistcessao.dtFim
                      AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                      AND tbhistcessao.dtInicio = (select max(dtInicio) from tbhistcessao where tbhistcessao.idServidor = tbservidor.idServidor)
                      AND situacao = 1
                      AND tbhistlot.lotacao = 113';
            if (!empty($idServidor)) {
                $select .= ' AND tbservidor.idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);

            # Cabeçalho da tabela
            $titulo = 'Servidor(es) cedido(s) pela UENF que terminaram a cessão mas ainda lotados na Reitoria - Cedidos';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Nome', 'Órgão', 'Início', 'Término', 'Lotação']);
            $tabela->set_align(['center', 'left', 'left', 'center', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, null, "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, null, "get_lotacao"]);
            $tabela->set_funcao([null, null, null, "date_to_php", "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Os servidores cedidos pela UENF que já terminaram"
                                . " o período de cessão deverão ser (re)lotados"
                                . " na universidade ou devem ter seu período de "
                                . "cessão renovado.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorEstatutarioSemCargo
     * 
     * Servidor estatutário sem cargo cadastrado:
     */
    public function get_servidorEstatutarioSemCargo($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "cadastro" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          tbpessoa.nome,        
                          idServidor,
                          tbperfil.nome,   
                          idServidor,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                    WHERE (idCargo IS null OR idCargo = 0)
                      AND situacao = 1
                      AND idPerfil = 1';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) ativos estatutário(s) sem cargo efetivo cadastrado.';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Nome', 'Lotação', 'Perfil', 'Cargo']);
            $tabela->set_align(['center', 'left', 'left', 'center']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, "Pessoal", null, "Pessoal"]);
            $tabela->set_metodo([null, null, "get_lotacao", null, "get_cargo"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("todo servidor estatutário concursado deve ter "
                                . "cadastrado no sistema o cargo ao qual prestou "
                                . "concurso.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorSemCargo
     * 
     * Servidor NÃO estatutário E NÃO bolsista sem cargo cadastrado:
     */
    public function get_servidorSemCargo($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "cadastro" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                    WHERE (idCargo IS null OR idCargo = 0)
                      AND situacao = 1
                      AND idPerfil <> 1';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) ativo não estatutário sem cargo cadastrado.';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Nome', 'Perfil', 'Lotação', 'Cargo']);
            $tabela->set_align(['center', 'left', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, "get_lotacao", "get_cargo"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorCedidoSemInfoCedente
     * 
     * Servidor cedido PARA a UENF sem informação do órgão cedente
     */
    public function get_servidorCedidoSemInfoCedente($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "cedidos" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT tbservidor.idFuncional,  
                          tbpessoa.nome,
                          tbcedido.orgaoOrigem,
                          tbservidor.idServidor,
                          tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbcedido USING (idServidor)
                                         JOIN tbpessoa USING (idPessoa)
                    WHERE (tbcedido.orgaoOrigem is null
                       OR tbcedido.orgaoOrigem = "")
                      AND situacao = 1
                      AND idPerfil = 2';
            if (!empty($idServidor)) {
                $select .= ' AND tbservidor.idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) ativo cedido(s) para UENF sem informações da cessão';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Nome', 'Órgão Cedente', 'Lotação']);
            $tabela->set_align(['center', 'left', 'left', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, "Pessoal"]);
            $tabela->set_metodo([null, null, null, "get_lotacao"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        $tabela->show();
                        callout("O servidor cedido psra a UENF deve ter cadastrado"
                                . " as informações da cessão.");
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorInativoComPerfilOutros
     * 
     * Servidor Inativo com perfil outros
     */
    public function get_servidorInativoComPerfilOutros($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "cadastro" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor,
                          tbsituacao.situacao,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                                     LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao)
                    WHERE idPerfil = 8
                      AND tbservidor.situacao <> 1';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) inativo(s) com perfil outros';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Perfil', 'Lotação', 'Cargo', 'Situação']);
            $tabela->set_align(['center', 'center', 'left', 'center', 'left', 'left', 'center']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_lotacao", "get_cargo"]);
            $tabela->set_funcao([null, "dv"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("O perfil outros foi definido na importação para"
                                . " servidores que estavam com perfil em branco."
                                . "<br/>Deve-se analisar para saber o real perfil"
                                . " desse servidor ou se não for servidor efetuar"
                                . " sua exclusão do sistema.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorInativoSemMotivoSaida
     * 
     * Servidor inativo sem motivo de saída:
     */
    public function get_servidorInativoSemMotivoSaida($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "cadastro" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor,
                          motivo,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)                                     
                    WHERE situacao <> 1
                      AND (motivo is null OR motivo = 0)';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) inativo(s) sem motivo de saída cadastrado.';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Perfil', 'Cargo', 'Situação', 'Motivo']);
            $tabela->set_align(['center', 'center', 'left', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_cargo", "get_situacao"]);
            $tabela->set_funcao([null, "dv"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorInativoSemdataSaida
     * 
     * Servidor inativo sem data de saída:
     */
    public function get_servidorInativoSemdataSaida($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "cadastro" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor,
                          dtDemissao,
                          idServidor 
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                    WHERE situacao <> 1
                      AND (dtDemissao IS null)';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) inativo(s) sem data de saída cadastrada.';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Perfil', 'Cargo', 'Situação', 'Saída']);
            $tabela->set_align(['center', 'center', 'left', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_cargo", "get_situacao"]);
            $tabela->set_funcao([null, "dv"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("É necessário cadastrar a data de saída do"
                                . "servidor inativo");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorDuplicado
     * 
     * Servidor Duplicado no Sistema
     */
    public function get_servidorDuplicado($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "cadastro" OR!empty($idServidor)) {
            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT tbservidor.idFuncional,
                          tbservidor.matricula,
                          tbpessoa.nome,
                          tbperfil.nome,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil) 
                     WHERE tbservidor.idServidor IN( SELECT tbservidor.idServidor
                                                       FROM tbservidor JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                                                       JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                                      WHERE tbservidor.situacao = 1
                                                        AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                                   GROUP BY tbservidor.idServidor
                                                     HAVING COUNT(*) > 1)';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) duplicado(s) no sistema.';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Perfil', 'Lotação']);
            $tabela->set_align(['center', 'center', 'left', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_lotacao"]);
            $tabela->set_funcao([null, "dv"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Verifique se não existem 2 lançamentos de lotação"
                                . " com o mesmo dia. Isso gera registros duplos em"
                                . " listagem onde é exibidda a lotação do servidor.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ###############################################################

    /**
     * Método get_servidorSemSituacao
     * 
     * Servidor sem situação cadastrada
     */
    public function get_servidorSemSituacao($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "cadastro" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          idServidor,                          
                          idServidor,
                          idServidor,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)                                     
                    WHERE situacao IS null OR situacao > 6';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) sem situacao cadastrada.';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Perfil', 'Cargo', 'Situação']);
            $tabela->set_align(['center', 'center', 'left', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, "Pessoal", "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, "get_perfil", "get_cargo", "get_situacao"]);
            $tabela->set_funcao([null, "dv"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorSemAdmissao
     * 
     * Servidor sem data de admissão
     */
    public function get_servidorSemAdmissao($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "cadastro" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          dtAdmissao
                          idServidor,                          
                          idServidor,
                          idServidor,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)                                     
                    WHERE dtadmissao IS null';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) sem data de admissão cadastrada.';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Admissão', 'Perfil', 'Cargo', 'Situação']);
            $tabela->set_align(['center', 'center', 'left', 'center', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal", "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_perfil", "get_cargo", "get_situacao"]);
            $tabela->set_funcao([null, "dv"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("É fundamental para o sistema o cadastro da data de "
                                . "admissão do servidor.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorSemProcessoPremio
     * 
     * Servidor estatutario ativo sem processo de Licença Premio (especial) 
     */
    public function get_servidorSemProcessoPremio($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "licencas" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          tbpessoa.nome,
                          idServidor,                          
                          idServidor,
                          idServidor,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)                                     
                    WHERE processoPremio IS null
                      AND idPerfil = 1
                      AND situacao = 1';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) estatutário(s) ativo(s) sem processo de ' . $servidor->get_licencaNome(6);

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Nome', 'Perfil', 'Cargo', 'Situação']);
            $tabela->set_align(['center', 'left', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, "Pessoal", "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, "get_perfil", "get_cargo", "get_situacao"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_feriasAntesAdmissao
     * 
     * Servidores com Férias anteriores a data de admissão
     */
    public function get_feriasAntesAdmissao($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "ferias" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT tbservidor.idFuncional,
                          tbpessoa.nome,
                          tbperfil.nome,
                          tbferias.anoExercicio,
                          tbferias.dtInicial,
                          tbferias.numDias,
                          tbservidor.dtAdmissao,
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbferias USING (idServidor)
                                     LEFT JOIN tbperfil USING (idPerfil)
                     WHERE tbservidor.situacao = 1
                       AND dtInicial < dtAdmissao';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY 2,4 desc';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Férias anteriores a data de Admissão do servidor';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Nome', 'Perfil', 'Ano Exercicio', 'Data Inicial', 'Dias', 'Admissão']);
            $tabela->set_align(['center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_funcao([null, null, null, null, "date_to_php", null, "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Nenhum servidor pode tirar férias antes de ser "
                                . "admitido. Alguma data está errada.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_licencaPremioEstranha
     * 
     * Servidores com Licença Prêmio com dias diferente de 30, 60 e 90 dias
     */
    public function get_licencaPremioEstranha($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "licencas" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT tbservidor.idFuncional,
                          tbpessoa.nome,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tblicencapremio.numDias,
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tblicencapremio USING (idServidor)
                     WHERE tbservidor.situacao = 1
                       AND idPerfil = 1
                       AND tblicencapremio.numDias <> 30
                       AND tblicencapremio.numDias <> 60
                       AND tblicencapremio.numDias <> 90';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidores com Licença Prêmio diferente de 30, 60 e 90 dias';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Dias']);
            $tabela->set_align(['center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, "get_cargo", "get_lotacao"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("A licença prêmio deve ter 30, 60 ou 90 dias. "
                                . "Valores diferentes podem ter sido causados na "
                                . "importação dos dados onde outro tipo de licença "
                                . "foi atribuido, erroneamente, como licença prêmio.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_estatutarioComLicencaMedicaClt
     * 
     * Servidor estatutario ativo com licença medica CLT
     */
    public function get_estatutarioComLicencaMedicaClt($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "licencas" OR!empty($idServidor)) {


            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          CASE alta
                            WHEN 1 THEN "Sim"
                            WHEN 2 THEN "Não"
                            end,
                         dtInicial,
                         numdias,
                         ADDDATE(dtInicial,numDias-1),
                         idServidor
                     FROM tblicenca JOIN tbservidor USING (idServidor)
                               LEFT JOIN tbpessoa USING (idPessoa)                                     
                    WHERE idTpLicenca = 21 
                      AND dtInicial>"2003-09-09"
                      AND idPerfil = 1';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) estatutário(s) com licença medica CLT';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Alta', 'Data Inicial', 'Dias', 'Data Final']);
            $tabela->set_align(['center', 'center', 'left', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_folgaFruidaTreMaiorConcedida
     * 
     * Servidores com Mais folgas fruídas do que concedidas
     */
    public function get_folgaFruidaTreMaiorConcedida($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "tre" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT tbservidor.idFuncional,
                          tbpessoa.nome,
                          tbperfil.nome,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                     WHERE tbservidor.situacao = 1
                       AND (SELECT sum(dias) FROM tbfolga WHERE tbfolga.idServidor = tbservidor.idServidor) > (SELECT sum(folgas) FROM tbtrabalhotre WHERE tbtrabalhotre.idServidor = tbservidor.idServidor)';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY 2,4 desc';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) com mais folgas fruídas do Tre do que concedidas';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Nome', 'Perfil', 'Lotação', 'Folgas Concedidas', 'Folgas Fruídas']);
            $tabela->set_align(['center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, "Pessoal", "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, "get_lotacao", "get_treFolgasConcedidas", "get_treFolgasFruidas"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_progressaoImportada
     * 
     * Servidores com progressão e/ou 
     */
    public function get_progressaoImportada($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "progressao" OR!empty($idServidor)) {


            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT distinct tbservidor.idFuncional,
                          tbpessoa.nome,
                          tbperfil.nome,
                          tbservidor.idServidor,
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                                     LEFT JOIN tbprogressao USING (idServidor)
                     WHERE situacao = 1 
                       AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                       AND idTpProgressao = 9';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) ativos com progressão importada';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Nome', 'Perfil', 'Lotação', 'Situação']);
            $tabela->set_align(['center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, "get_lotacao", "get_situacao"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_progressaoImportada
     * 
     * Servidores com progressão e/ou 
     */
    public function get_progressaoImportadaInativos($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "progressao" OR!empty($idServidor)) {


            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT distinct tbservidor.idFuncional,
                          tbservidor.matricula,
                          tbpessoa.nome,
                          tbperfil.nome,
                          tbservidor.idServidor,
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                                     LEFT JOIN tbprogressao USING (idServidor)
                     WHERE situacao <> 1 
                       AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                       AND idTpProgressao = 9';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) INATIVOS com progressão importada';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Perfil', 'Lotação', 'Situação']);
            $tabela->set_align(['center', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_funcao([null, "dv"]);
            $tabela->set_classe([null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_lotacao", "get_situacao"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_celetistaInativoFimCessao
     * 
     * Celetista com situação Fim de Cessão
     */
    public function get_celetistaInativoFimCessao($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "cadastro" OR!empty($idServidor)) {


            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)                                     
                    WHERE situacao = 6 AND idPerfil = 4';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Celetista(s) com situação Fim de Cessão.';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Perfil', 'Cargo', 'Situação']);
            $tabela->set_align(['center', 'center', 'left', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_cargo", "get_situacao"]);
            $tabela->set_funcao([null, "dv"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("A situação FIM DE CESSÃO é somente para servidores"
                                . " cedidos que terminaram a cessão e não para"
                                . " celetistas");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorSemSexo
     * 
     * Servidor sem Sexo Cadastrado
     */
    public function get_servidorSemSexo($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "cadastro" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)                                     
                    WHERE sexo is null';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor sem sexo cadastrado no sistema.';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Perfil', 'Cargo', 'Situação']);
            $tabela->set_align(['center', 'center', 'left', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_cargo", "get_situacao"]);
            $tabela->set_funcao([null, "dv"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorSemSexo
     * 
     * Servidor sem Sexo Cadastrado
     */
    public function get_servidorSemEstCiv($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "cadastro" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)                                     
                    WHERE estciv is null';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor sem estado civil cadastrado no sistema.';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Perfil', 'Cargo', 'Situação']);
            $tabela->set_align(['center', 'center', 'left', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_cargo", "get_situacao"]);
            $tabela->set_funcao([null, "dv"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorComAverbacaoAposAdmissao
     * 
     * Servidor sem Sexo Cadastrado
     */
    public function get_servidorComAverbacaoAposAdmissao($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "aposentadoria" OR!empty($idServidor)) {


            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor,
                          tbservidor.dtAdmissao,
                          tbaverbacao.dtInicial
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                                     LEFT JOIN tbaverbacao USING (idServidor)
                    WHERE tbservidor.dtAdmissao < tbaverbacao.dtInicial';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) com tempo averbado iniciando após admissão.';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Perfil', 'Cargo', 'Situação', 'Admissão', 'Data da Averbação']);
            $tabela->set_align(['center', 'center', 'left', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_cargo", "get_situacao"]);
            $tabela->set_funcao([null, "dv", null, null, null, null, "date_to_php", "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorComAverbacaoTerminandoAposAdmissao
     * 
     * Servidor sem Sexo Cadastrado
     */
    public function get_servidorComAverbacaoTerminandoAposAdmissao($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "aposentadoria" OR!empty($idServidor)) {


            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor,
                          tbservidor.dtAdmissao,
                          tbaverbacao.dtFinal
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                                     LEFT JOIN tbaverbacao USING (idServidor)
                    WHERE tbservidor.dtAdmissao < tbaverbacao.dtFinal';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) com tempo averbado terminando após admissão.';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Perfil', 'Cargo', 'Situação', 'Admissão', 'Data Final da Averbação']);
            $tabela->set_align(['center', 'center', 'left', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_cargo", "get_situacao"]);
            $tabela->set_funcao([null, "dv", null, null, null, null, "date_to_php", "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ############################################################

    /**
     * Método get_servidorComDependentesSemParentesco
     * 
     * Servidor Com Dependentes (parentes) sem parentesco cadastrado
     */
    public function get_servidorComDependentesSemParentesco($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "cadastro" OR!empty($idServidor)) {


            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT tbdependente.nome,
                          tbparentesco.Parentesco,
                          idfuncional,
                          matricula,
                          tbpessoa.nome,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)                                     
                                     JOIN tbdependente USING (idPessoa)
                                     LEFT JOIN tbparentesco ON (tbparentesco.idParentesco = tbdependente.parentesco)
                    WHERE tbdependente.parentesco IS null ';

            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDEr BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) com parentes sem parentesco cadastrado.';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['Parente', 'Parentesco', 'ID Funcional', 'Matrícula', 'Servidor']);
            $tabela->set_align(['left', 'center', 'center', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("É necessário definir o típo de parentesco entre "
                                . "o parente e o servidor");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorComTerminoReadaptacaoMenos45Dias
     * 
     * Servidor Com Readaptação terminando em menos de 45 dias
     */
    public function get_servidorComTerminoReadaptacaoMenos45Dias($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "beneficios" OR!empty($idServidor)) {


            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbperfil.nome,
                          idServidor,
                          idServidor,
                          DATE_SUB(ADDDATE(tbreadaptacao.dtInicio, INTERVAL tbreadaptacao.periodo MONTH),INTERVAL 1 DAY),
                          TIMESTAMPDIFF(DAY,CURRENT_DATE,DATE_SUB(ADDDATE(tbreadaptacao.dtInicio, INTERVAL tbreadaptacao.periodo MONTH),INTERVAL 1 DAY))
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                                     LEFT JOIN tbreadaptacao USING (idServidor)
                    WHERE tbreadaptacao.dtInicio IS NOT null
                      AND TIMESTAMPDIFF(DAY,CURRENT_DATE,DATE_SUB(ADDDATE(tbreadaptacao.dtInicio, INTERVAL tbreadaptacao.periodo MONTH),INTERVAL 1 DAY)) >= 0 
                      AND TIMESTAMPDIFF(DAY,CURRENT_DATE,DATE_SUB(ADDDATE(tbreadaptacao.dtInicio, INTERVAL tbreadaptacao.periodo MONTH),INTERVAL 1 DAY)) <=45';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY 7 desc';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Readaptação terminando em menos de 45 dias.';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Perfil', 'Cargo', 'Lotação', 'Data Final', 'Dias Faltantes']);
            $tabela->set_align(['center', 'center', 'left', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_cargo", "get_lotacao"]);
            $tabela->set_funcao([null, "dv", null, null, null, null, "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorComTerminoReducaoMenos45Dias
     * 
     * Servidor Com Redução terminando em menos de 45 dias
     */
    public function get_servidorComTerminoReducaoMenos45Dias($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "beneficios" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbperfil.nome,
                          idServidor,
                          idServidor,
                          DATE_SUB(ADDDATE(tbreducao.dtInicio, INTERVAL tbreducao.periodo MONTH),INTERVAL 1 DAY),
                          TIMESTAMPDIFF(DAY,CURRENT_DATE,DATE_SUB(ADDDATE(tbreducao.dtInicio, INTERVAL tbreducao.periodo MONTH),INTERVAL 1 DAY))
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                                     LEFT JOIN tbreducao USING (idServidor)
                    WHERE tbreducao.dtInicio IS NOT null
                      AND TIMESTAMPDIFF(DAY,CURRENT_DATE,DATE_SUB(ADDDATE(tbreducao.dtInicio, INTERVAL tbreducao.periodo MONTH),INTERVAL 1 DAY)) >= 0 
                      AND TIMESTAMPDIFF(DAY,CURRENT_DATE,DATE_SUB(ADDDATE(tbreducao.dtInicio, INTERVAL tbreducao.periodo MONTH),INTERVAL 1 DAY)) <=45';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY 7 desc';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Redução da CH terminando em menos de 45 dias.';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Perfil', 'Cargo', 'Lotação', 'Data Final', 'Dias Faltantes']);
            $tabela->set_align(['center', 'center', 'left', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_cargo", "get_lotacao"]);
            $tabela->set_funcao([null, "dv", null, null, null, null, "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorComTerminoLicencaSemVencimentosMenos90Dias
     * 
     * Servidor Com Licença Sem Vencimentos terminando em menos de 90 dias
     */
    public function get_servidorComTerminoLicencaSemVencimentosMenos90Dias($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "licencas" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbperfil.nome,
                          idServidor,
                          idServidor,
                          CONCAT(tbtipolicenca.nome,"<br/>",IFnull(tbtipolicenca.lei,"")),
                          DATE_SUB(ADDDATE(tblicenca.dtInicial, INTERVAL tblicenca.numDias DAY),INTERVAL 1 DAY),
                          TIMESTAMPDIFF(DAY,CURRENT_DATE,DATE_SUB(ADDDATE(tblicenca.dtInicial, INTERVAL tblicenca.numDias DAY),INTERVAL 1 DAY))
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tblicenca USING (idServidor)
                                     LEFT JOIN tbtipolicenca USING (idTpLicenca)
                                     LEFT JOIN tbperfil USING (idPerfil)
                    WHERE tblicenca.dtInicial IS NOT null
                      AND (idTpLicenca = 5 OR idTpLicenca = 8 OR idTpLicenca = 16)
                      AND TIMESTAMPDIFF(DAY,CURRENT_DATE,DATE_SUB(ADDDATE(tblicenca.dtInicial, INTERVAL tblicenca.numDias DAY),INTERVAL 1 DAY)) >= 0 
                      AND TIMESTAMPDIFF(DAY,CURRENT_DATE,DATE_SUB(ADDDATE(tblicenca.dtInicial, INTERVAL tblicenca.numDias DAY),INTERVAL 1 DAY)) <=90';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY 7 desc';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Licença Sem Vencimentos terminando em menos de 90 dias.';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Perfil', 'Cargo', 'Lotação', 'Licença', 'Data Final', 'Dias Faltantes']);
            $tabela->set_align(['center', 'center', 'left', 'center', 'left', 'left', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_cargo", "get_lotacao"]);
            $tabela->set_funcao([null, "dv", null, null, null, null, null, "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorInativoComCargoEmComissao
     * 
     * Servidor Inativo com cargo em comissao
     */
    public function get_servidorInativoComCargoEmComissao($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "comissao" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbperfil.nome,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbcomissao.dtNom,
                          tbcomissao.dtExo,
                          tbsituacao.situacao,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                                     LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao)
                                      JOIN tbcomissao USING (idServidor)
                    WHERE tbservidor.situacao <> 1
                      AND ((CURRENT_DATE BETWEEN tbcomissao.dtNom AND tbcomissao.dtExo)
                       OR (tbcomissao.dtExo is null))';

            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) inativo(s) com cargo em comissao ainda vigente';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Perfil', 'Lotação', 'Cargo', 'Nomeação', 'Exoneração', 'Situação']);
            $tabela->set_align(['center', 'center', 'left', 'center', 'left', 'left', 'center']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_lotacao", "get_cargo"]);
            $tabela->set_funcao([null, "dv", null, null, null, null, "date_to_php", "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Deve-se exonerar o servidor do cargo em comissão "
                                . "antes de aposentar, demitir, exonerá-lo");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorAtivoLicencaMedicaEmAberto
     * 
     * Servidor Com a ultima licença médica em aberto
     */
    public function get_servidorAtivoUltimaLicencaMedicaEmAberto($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "licencas" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          tbpessoa.nome,
                          t1.idServidor,
                          t1.idServidor,
                          idServidor
                     FROM tbservidor AS t1 JOIN tbpessoa USING (idPessoa)
                    WHERE situacao = 1
                      AND 
                          (SELECT alta
                             FROM tblicenca AS t2 
                            WHERE (idTpLicenca = 1 OR idTpLicenca = 30) 
                              AND t2.idServidor = t1.idServidor
                         ORDER BY dtInicial DESC LIMIT 1) <> 1';

            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) Ativos com a Última Licença Médica em Aberto (sem alta)';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Nome', 'Lotação', 'Cargo']);
            $tabela->set_align(['center', 'left', 'left', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, "get_lotacao", "get_cargo"]);
            #$tabela->set_funcao([null, "dv", null, null, null, null, "date_to_php", "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Na relação abaixo, "
                                . "somente está incluída a última licença "
                                . "médica de cada servidor cujo campo alta esteja"
                                . " em branco ou com o valor 'NÃO'.<br/>"
                                . "As licenças para o tratamento de saúde de "
                                . "parentes não são consideradas nesta listagem.<br/>"
                                . "Atente para o fato que caso o servidor tenha "
                                . "a última licença médica em aberto, o sistema "
                                . "não permitirá o cadastro de mais nenhuma "
                                . "licença posterior.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorAtivoLicencaMedicaSemAlta
     * 
     * Servidor Com licença médica sem alta cadastrada
     */
    public function get_servidorAtivoLicencaMedicaSemAlta($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "licencas" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT distinct idfuncional,
                          tbpessoa.nome,
                          t1.idServidor,
                          t1.idServidor,
                          idServidor
                     FROM tbservidor AS t1 JOIN tbpessoa USING (idPessoa)
                                           JOIN tblicenca USING (idServidor)
                    WHERE situacao = 1
                      AND alta <> 1 
                      AND alta <> 2
                      AND (idTpLicenca = 1 OR idTpLicenca = 30 OR idTpLicenca = 2)';

            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) Ativos com Licença Médica em Aberto (sem alta)';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Nome', 'Lotação', 'Cargo']);
            $tabela->set_align(['center', 'left', 'left', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, "get_lotacao", "get_cargo"]);
            #$tabela->set_funcao([null, "dv", null, null, null, null, "date_to_php", "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Na relação abaixo estão incluídas TODAS as licenças "
                                . "médicas cujo campo alta esteja EM BRANCO, ou seja, "
                                . "NÃO PREENCHIDO<br/>Atente que, para esta listagem, "
                                . "as licenças para o tratamento de saúde de "
                                . "parentes são consideradas.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorAtivoSemEscolha
     * 
     * Informa os servidores anteriores a separação FENORTE x UENF que não foi
     * cadastrado no sistema a opção voluntária de transferência para a Uenf
     */
    public function get_servidorAtivoSemEscolha($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "cadastro" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                    WHERE tbservidor.situacao = 1
                      AND matricula < 10000
                      AND (idPerfil = 1 OR idPerfil = 4)
                      AND opcaoFenorteUenf IS NULL';

            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) ativo(s) cuja opção de transferência para Uenf não foi cadastrada';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Lotação', 'Cargo']);
            $tabela->set_align(['center', 'center', 'left', 'left', 'left', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, "get_lotacao", "get_cargo"]);
            $tabela->set_funcao([null, "dv"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Deve-se cadastrar se o servidor optou Sim ou Não para a transferência para a Uenf");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorNaoAtivoSemEscolha
     * 
     * Informa os servidores não ativos anteriores a separação FENORTE x UENF que não foi
     * cadastrado no sistema a opção voluntária de transferência para a Uenf
     */
    public function get_servidorNaoAtivoSemEscolha($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "cadastro" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                    WHERE tbservidor.situacao <> 1
                      AND matricula < 10000
                      AND (idPerfil = 1 OR idPerfil = 4)
                      AND opcaoFenorteUenf IS NULL';

            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Servidor(es) inativo(s) cuja opção de transferência para Uenf não foi cadastrada';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Lotação', 'Cargo']);
            $tabela->set_align(['center', 'center', 'left', 'left', 'left', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, "get_lotacao", "get_cargo"]);
            $tabela->set_funcao([null, "dv"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Deve-se cadastrar se o servidor optou Sim ou Não para a transferência para a Uenf");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorAdmTecConcursoNao2012AdmitidoDepois2012
     * 
     * Servidor Concursado com concurso posterior a admissão
     */
    public function get_servidorAdmTecConcursoNao2012AdmitidoDepois2012($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "concurso" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          dtPublicacaoEdital,
                          dtAdmissao,                          
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor,
                          tbsituacao.situacao,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                                     LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao)
                                     LEFT JOIN tbconcurso USING (idConcurso)
                    WHERE dtAdmissao > (SELECT dtPublicacaoEdital FROM tbconcurso WHERE idConcurso = 3)
                      AND idConcurso <> 3
                      AND (idPerfil = 1 OR idPerfil = 4)
                      AND (idCargo <> 128 AND idCargo <> 129)';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY dtAdmissao,tbpessoa.nome';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = "Servidor(es) concursado(s) antes do concurso de 2012 admitido(s) depois deste concurso";

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Concurso', 'Admissão', 'Nome', 'Perfil', 'Lotação', 'Cargo', 'Situação']);
            $tabela->set_align(['center', 'center', 'center', 'left', 'center', 'left', 'left', 'center']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, null, "get_lotacao", "get_cargo"]);
            $tabela->set_funcao([null, "date_to_php", "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Servidor concursado antes do concurso de 2012 não pode ser admitido "
                                . "depois deste concurso.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorComReadaptacaoVigenteTerminada
     * 
     * Servidor Com Readaptação vigente terminada
     */
    public function get_servidorComReadaptacaoVigenteTerminada($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "beneficios" OR!empty($idServidor)) {


            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbperfil.nome,
                          idServidor,
                          idServidor,
                          DATE_SUB(ADDDATE(tbreadaptacao.dtInicio, INTERVAL tbreadaptacao.periodo MONTH),INTERVAL 1 DAY),
                          TIMESTAMPDIFF(DAY,CURRENT_DATE,DATE_SUB(ADDDATE(tbreadaptacao.dtInicio, INTERVAL tbreadaptacao.periodo MONTH),INTERVAL 1 DAY))
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                                     LEFT JOIN tbreadaptacao USING (idServidor)
                    WHERE tbreadaptacao.dtInicio IS NOT null
                      AND tbreadaptacao.status = 2
                      AND TIMESTAMPDIFF(DAY,CURRENT_DATE,DATE_SUB(ADDDATE(tbreadaptacao.dtInicio, INTERVAL tbreadaptacao.periodo MONTH),INTERVAL 1 DAY)) < 0';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY 7 desc';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Readaptação vigente já terminada !!.';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Perfil', 'Cargo', 'Lotação', 'Data Final', 'Dias Faltantes']);
            $tabela->set_align(['center', 'center', 'left', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_cargo", "get_lotacao"]);
            $tabela->set_funcao([null, "dv", null, null, null, null, "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorComTerminoReducaoVigenteTerminada
     * 
     * Servidor Com Redução vigente terminada
     */
    public function get_servidorComTerminoReducaoVigenteTerminada($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "beneficios" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbperfil.nome,
                          idServidor,
                          idServidor,
                          DATE_SUB(ADDDATE(tbreducao.dtInicio, INTERVAL tbreducao.periodo MONTH),INTERVAL 1 DAY),
                          TIMESTAMPDIFF(DAY,CURRENT_DATE,DATE_SUB(ADDDATE(tbreducao.dtInicio, INTERVAL tbreducao.periodo MONTH),INTERVAL 1 DAY))
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                                     LEFT JOIN tbreducao USING (idServidor)
                    WHERE tbreducao.dtInicio IS NOT null
                      AND tbreducao.status = 2
                      AND TIMESTAMPDIFF(DAY,CURRENT_DATE,DATE_SUB(ADDDATE(tbreducao.dtInicio, INTERVAL tbreducao.periodo MONTH),INTERVAL 1 DAY)) < 0';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY 7 desc';

            $result = $servidor->select($select);
            $count = $servidor->count($select);
            $titulo = 'Redução da CH vigente já terminada !!.';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Matrícula', 'Nome', 'Perfil', 'Cargo', 'Lotação', 'Data Final', 'Dias Faltantes']);
            $tabela->set_align(['center', 'center', 'left', 'center', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, null, null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, null, null, null, "get_cargo", "get_lotacao"]);
            $tabela->set_funcao([null, "dv", null, null, null, null, "date_to_php"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_servidorPublicacaoPremioPendente
     * 
     * Servidores com publicação de licença premio pendente
     */
    public function get_servidorPublicacaoPremioPendente($idServidor = null, $catEscolhida = null) {

        if (empty($catEscolhida) OR $catEscolhida == "licencas" OR!empty($idServidor)) {

            $servidor = new Pessoal();
            $metodo = explode(":", __METHOD__);

            $select = 'SELECT tbservidor.idFuncional,
                          tbpessoa.nome,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                     WHERE tbservidor.situacao = 1
                       AND idPerfil = 1';
            if (!empty($idServidor)) {
                $select .= ' AND idServidor = "' . $idServidor . '"';
            }
            $select .= ' ORDER BY tbpessoa.nome';
            $result = $servidor->select($select);
            $count = 0;
            
            $result2 = null;

            # Percorre o array e retira os servidores com 0 publicações pendentes
            $licenca = new LicencaPremio();
            foreach ($result as $tt) {
                if ($licenca->get_numPublicacoesFaltantesTotal($tt[6]) <> 0) {
                    $result2[] = $tt;
                    $count++;
                }
            }
            
            $titulo = 'Servidor(es) com Publicação(ões) de Licença Especial (Prêmio) Pendente(s)';

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result2);
            $tabela->set_label(['IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Publicações Possíveis', 'Publicações Efetuadas', 'Publicações Pendentes']);
            $tabela->set_width([8, 22, 25, 25, 5, 5, 5]);
            $tabela->set_align(['center', 'left', 'left', 'left']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, null, "Pessoal", "Pessoal", "LicencaPremio", "LicencaPremio", "LicencaPremio"]);
            $tabela->set_metodo([null, null, "get_cargo", "get_lotacao", "get_numPublicacoesPossiveisTotal", "get_numPublicacoesTotal", "get_numPublicacoesFaltantesTotal"]);
            $tabela->set_editar($this->linkEditar);
            $tabela->set_idCampo('idServidor');

            # Verifica se é de um único servidor
            if (!empty($idServidor)) {
                if ($count > 0) {
                    return $titulo;
                }
            } else {  # Vários servidores
                if ($this->lista) {
                    if ($count > 0) {
                        callout("Considerando TODOS os vínculos do servidor.");
                        $tabela->show();
                        set_session('origem', "alertas.php?fase=tabela&alerta=" . $metodo[2]);
                    } else {
                        br();
                        tituloTable($titulo);
                        $callout = new Callout();
                        $callout->abre();
                        p('Nenhum item encontrado !!', 'center');
                        $callout->fecha();
                    }
                } else {
                    if ($count > 0) {
                        $retorna = [$count . ' ' . $titulo, $metodo[2], $catEscolhida];
                        return $retorna;
                    }
                }
            }
        }
    }

    ##########################################################
}
