<?php

class CheckupOld {

    /**
     * Classe Checup
     * 
     * Faz um checup no banco de dados pessoal a procura de erros quanto ao banco de dados quanto as regras de negócio
     * 
     * By Alat
     */
    private $lista = true;       // Informa se será listagem ou somente contagem dos registros
    private $linkEditar = 'servidor.php?fase=editar&id=';

    ###########################################################

    /**
     * Método construct
     * 
     * Faz um checkup
     */
    public function __construct($lista = true) {
        $this->lista = $lista;
    }

    ###########################################################

    public function set_linkEditar($linkEditar = null) {
        if (!empty($linkEditar)) {
            $this->linkEditar = $linkEditar;
        }
    }

    ###########################################################

    /**
     * Método get_all
     * 
     * Executa todos os métodos desta classe (menos é claro o get_all e o construct
     */
    public function get_all() {
        # Calcula o tempo
        $time_start = microtime(true);

        # Pega todos as informações da classe
        $api = new ReflectionClass($this);

        # Inicia um array para guardar o retorno 
        $metodoRetorno = array();

        # Percorre todos os métodos da classe e guarda no array seu retorno
        foreach ($api->getMethods() as $method) {
            if (($method->getName() <> 'get_all')
                    AND ($method->getName() <> '__construct')
                    AND ($method->getName() <> 'set_linkEditar')) {
                $metodo = $method->getName();
                if (!empty($this->$metodo())) {
                    $metodoRetorno[] = $this->$metodo();
                }
            }
        }

        # Ordena os métodos pela categoria

        function cmp($a, $b) {
            # Função específica que compara se $a é maior que $b
            return $a[2] > $b[2];
        }

        // Ordena
        usort($metodoRetorno, 'cmp');

        $categoriaAnterior = null;

        # Percorre o array $metodoRetorno e exibe a lista
        foreach ($metodoRetorno as $listaRetorno) {

            # Exibe uma linha horizontal
            if ($categoriaAnterior <> $listaRetorno[2]) {
                if (empty($categoriaAnterior)) {
                    $categoriaAnterior = $listaRetorno[2];
                    p($categoriaAnterior, "checkCategoria");
                } else {
                    $categoriaAnterior = $listaRetorno[2];
                    hr("alerta");
                    p($categoriaAnterior, "checkCategoria");
                }
            }

            $link = new Link($listaRetorno[0], "?fase=alerta&alerta=" . $listaRetorno[1]);

            # Define a cor de acordo com a prioridade
            if (empty($listaRetorno[3])) {
                $link->set_id("checkupResumo");
            } else {
                $link->set_id("checkupResumo" . $listaRetorno[3]);
            }

            echo "<li>";
            $link->show();
            echo "</li>";
        }

        hr("alerta");

        # Exibe o tempo decorrido
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        p(number_format($time, 4, '.', ',') . " segundos", "right", "f10");
    }

    ###########################################################

    /**
     * Método get_licencaVencendo
     * 
     * Servidores com Licença vencendo este ano
     */
    public function get_licencaVencendo($idServidor = null, $catEscolhida = null) {

        # Define a categoria e a categoria
        $prioridade = null;
        $categoria = "Licenças";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_trienioVencendo
     * 
     * Servidores com trênio vencendo este ano
     */
    public function get_trienioVencendo($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Triênio";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_trienioVencido
     * 
     * Servidores com trênio vencido anterior a esse ano
     */
    public function get_trienioVencido($idServidor = null) {

        # Define a categoria e a categoria
        $prioridade = null;
        $categoria = "Triênio";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_auxilioCrecheVencido
     * 
     * Servidores com o auxílio creche vencendo este ano
     */
    public function get_auxilioCrecheVencido($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Auxílio Creche";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_motoristaCarteiraVencida
     * 
     * Motoristas com carteira de habilitação vencida no sistema
     */
    public function get_motoristaCarteiraVencida($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Motorista";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("Solicitar aos motoristas que compareçam a GRH com a cópia da carteira para ser arquivada.<br/>Lembre-se de cadastrar no sistema, na área de documentos do motorista, a nova data, senão esta mensagem continuará sendo exibida para esse servidor.");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_motoristaSemDataCarteira
     * 
     * Motoristas com carteira de habilitação sem data de vencimento cadastrada no sistema
     */
    public function get_motoristaSemDataCarteira($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Motorista";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("Solicitar aos motoristas que compareçam a GRH com a cópia da carteira para ser arquivada. Lembre-se de cadastrar no sistema, na área de documentos do motorista, a data da carteira, senão esta mensagem continuará sendo exibida para esse servidor.");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_motoristaSemCarteira
     * 
     * Motorista sem número da carteira de habilitação cadastrada:
     */
    public function get_motoristaSemCarteira($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Motorista";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("Solicitar aos motoristas que compareçam a GRH com a cópia da carteira para ser arquivada. Lembre-se de cadastrar no sistema, na área de documentos do motorista, os dados da carteira de habilitação, senão esta mensagem continuará sendo exibida para esse servidor.");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorCom74
     * 
     * Servidor estatutário que faz 75 anos este ano (Preparar aposentadoria compulsória)
     */
    public function get_servidorCom74($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Aposentadoria";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("Avisar ao servidor sobre a aposentadoria compulsória.");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorComMais74
     * 
     * Servidor estatutário com 75 anos ou mais (Aposentar Compulsoriamente)
     */
    public function get_servidorComMais75($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Aposentadoria";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorComMaisde1MatriculaAtiva
     * 
     * Servidor estatutário com mais de uma matriculka ativa
     */
    public function get_servidorComMaisde1MatriculaAtiva($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = 1;
        $categoria = "Cadastro Geral";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("Servidor com 2 matriculas Ativas !! Houve algum erro no sistema, favor verificar. Somente uma matrícula deveria estar ativa");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorComPerfilOutros
     * 
     * Servidor Ativo com perfil outros
     */
    public function get_servidorComPerfilOutros($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Cadastro Geral";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("O perfil outros foi definido na importação para servidores que estavam com perfil em branco. Deve-se analisar para saber o real perfil desse servidor ou se não for servidor efetuar sua exclusão do sistema.");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorSemPerfil
     * 
     * Servidor com perfil outros
     */
    public function get_servidorSemPerfil($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = 1;
        $categoria = "Perfil";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("Algum erro no sistema, favor verificar. Todos os servidores devem tem um perfil cadastrado.");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorTecnicoEstatutarioSemConcurso
     * 
     * Servidor Concursado sem concurso cadastrado
     */
    public function get_servidorTecnicoEstatutarioInativosSemConcurso($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Concurso";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("Todo servidor concursado deve ter cadastrado o concurso no qual foi aprovado.");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorTecnicoEstatutarioSemConcurso
     * 
     * Servidor Concursado sem concurso cadastrado
     */
    public function get_servidorTecnicoCeletistaInativosSemConcurso($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Concurso";

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
                      AND (idCargo <> 128 AND idCargo <> 129)';
        if (!empty($idServidor)) {
            $select .= ' AND idServidor = "' . $idServidor . '"';
        }
        $select .= ' ORDER BY dtAdmissao,tbpessoa.nome';


        $result = $servidor->select($select);
        $count = $servidor->count($select);
        $titulo = 'Servidor(es) técnico(s) celetista(s) inativos sem concurso cadastrado';

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("Todo servidor concursado deve ter cadastrado o concurso no qual foi aprovado.");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorTecnicoEstatutarioSemConcurso
     * 
     * Servidor Concursado sem concurso cadastrado
     */
    public function get_servidorTecnicoAtivosEstatutarioSemConcurso($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Concurso";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("Todo servidor concursado deve ter cadastrado o concurso no qual foi aprovado.");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorAtivoComConcursoPosteriorAdmissao
     * 
     * Servidor Concursado com concurso posterior a admissão
     */
    public function get_servidorAtivoComConcursoPosteriorAdmissao($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = 1;
        $categoria = "Concurso";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("Um servidor concursado não pode ser admitido antes de efetivamente passar no concurso.");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorInativoComConcursoPosteriorAdmissao
     * 
     * Servidor Concursado com concurso posterior a admissão
     */
    public function get_servidorInativoComConcursoPosteriorAdmissao($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Concurso";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("Todo servidor concursado deve ter cadastrado o concurso no qual foi aprovado.");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorProfessorAtivoSemConcurso
     * 
     * Servidor Concursado sem concurso cadastrado
     */
    public function get_servidorProfessorAtivoSemConcurso($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Concurso";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("Todo servidor concursado deve ter cadastrado o concurso no qual foi aprovado.");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorProfessorAtivoSemConcurso
     * 
     * Servidor Concursado sem concurso cadastrado
     */
    public function get_servidorProfessorInativoSemConcurso($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Concurso";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("Todo servidor concursado deve ter cadastrado o concurso no qual foi aprovado.");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_cargoComissaoNomeacaoIgualExoneracao
     * 
     * Cargo em comissão nomeado e exonerado no mesmo dia?!
     */
    public function get_cargoComissaoNomeacaoIgualExoneracao($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Cargo em Comissão";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("Cargo em comissão nomeado e exonerado no mesmo dia.");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorSemIdFuncional
     * 
     * Exibe servidor ativo sem id Funcional cadastrado que não for bolsista
     */
    public function get_servidorSemIdFuncional($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Cadastro Geral";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                #callout("Servidor sem Id Funcional cadastrado no Sistema");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorSemDtNasc
     * 
     * Servidor sem data de nasciment cadastrada
     */
    public function get_servidorSemDtNasc($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Cadastro Geral";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("O cadastro da data de nascimento do servidor é necessário para diversas rotinas do sistema. Verifique se na pasta do arquivo não tem nenhuma cópia de documento que tenha essa informação.");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorCedidoLotacaoErrada
     * 
     * Servidor DA UENF cedido a outro orgão que não está lotado na reitoria cedidos
     */
    public function get_servidorCedidoLotacaoErrada($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Cedidos";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("O servidor cedido pela UENF deve estar cadastrado no setor Reitoria - Cessão.");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorCedidoDataExpirada
     * 
     * Servidor DA UENF cedido a outro orgão onde a dta de término de cassão já passou mas continua cedido na reitoria cedidos
     */
    public function get_servidorCedidoDataExpirada($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Cedidos";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("Os servidores cedidos pela UENF que já terminaram o período de cessão deverão ser (re)lotados na universidade ou devem ter seu período de cessão renovado.");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorEstatutarioSemCargo
     * 
     * Servidor estatutário sem cargo cadastrado:
     */
    public function get_servidorEstatutarioSemCargo($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Cadastro Geral";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                #callout("Servidor sem Id Funcional cadastrado no Sistema");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorSemCargo
     * 
     * Servidor NÃO estatutário E NÃO bolsista sem cargo cadastrado:
     */
    public function get_servidorSemCargo($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Cadastro Geral";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                #callout("Servidor sem Id Funcional cadastrado no Sistema");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorCedidoSemInfoCedente
     * 
     * Servidor cedido PARA a UENF sem informação do órgão cedente
     */
    public function get_servidorCedidoSemInfoCedente($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Cedidos";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("O servidor cedido psra a UENF deve ter cadastrado as informações da cessão.");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorInativoComPerfilOutros
     * 
     * Servidor Inativo com perfil outros
     */
    public function get_servidorInativoComPerfilOutros($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Cadastro Geral";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("O perfil outros foi definido na importação para servidores que estavam com perfil em branco.<br/>Deve-se analisar para saber o real perfil desse servidor ou se não for servidor efetuar sua exclusão do sistema.");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorInativoSemMotivoSaida
     * 
     * Servidor inativo sem motivo de saída:
     */
    public function get_servidorInativoSemMotivoSaida($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Cadastro Geral";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                #callout("Servidor sem Id Funcional cadastrado no Sistema");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorInativoSemdataSaida
     * 
     * Servidor inativo sem data de saída:
     */
    public function get_servidorInativoSemdataSaida($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Cadastro Geral";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                #callout("Servidor sem Id Funcional cadastrado no Sistema");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorDuplicado
     * 
     * Servidor Duplicado no Sistema
     */
    public function get_servidorDuplicado($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = 1;
        $categoria = "Cadastro Geral";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("Verifique se não existem 2 lançamentos de lotação com o mesmo dia. Isso gera registros duplos em listagem onde é exibidda a lotação do servidor.");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorSemSituacao
     * 
     * Servidor sem situação cadastrada
     */
    public function get_servidorSemSituacao($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = 1;
        $categoria = "Cadastro Geral";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                #callout("Servidor sem Id Funcional cadastrado no Sistema");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorSemAdmissao
     * 
     * Servidor sem data de admissão
     */
    public function get_servidorSemAdmissao($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = 1;
        $categoria = "Cadastro Geral";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                #callout("Servidor sem Id Funcional cadastrado no Sistema");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorSemProcessoPremio
     * 
     * Servidor estatutario ativo sem processo de Licença Premio (especial) 
     */
    public function get_servidorSemProcessoPremio($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Licenças";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                #callout("Servidor sem Id Funcional cadastrado no Sistema");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_feriasAntesAdmissao
     * 
     * Servidores com Férias anteriores a data de admissão
     */
    public function get_feriasAntesAdmissao($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Férias";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_licencaPremioEstranha
     * 
     * Servidores com Licença Prêmio com dias diferente de 30, 60 e 90 dias
     */
    public function get_licencaPremioEstranha($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Licenças";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("A licença prêmio deve ter 30, 60 ou 90 dias. Valores diferentes podem ter sido causados na importação dos dados onde outro tipo de licença foi atribuido, erroneamente, como licença prêmio.");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_estatutarioComLicencaMedicaClt
     * 
     * Servidor estatutario ativo com licença medica CLT
     */
    public function get_estatutarioComLicencaMedicaClt($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Licenças";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                #callout("Servidor sem Id Funcional cadastrado no Sistema");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_folgaFruidaTreMaiorConcedida
     * 
     * Servidores com Mais folgas fruídas do que concedidas
     */
    public function get_folgaFruidaTreMaiorConcedida($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = 1;
        $categoria = "TRE";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_progressaoImportada
     * 
     * Servidores com progressão e/ou 
     */
    public function get_progressaoImportada($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Progressão";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_progressaoImportada
     * 
     * Servidores com progressão e/ou 
     */
    public function get_progressaoImportadaInativos($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Progressão";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_celetistaInativoFimCessao
     * 
     * Celetista com situação Fim de Cessão
     */
    public function get_celetistaInativoFimCessao($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Cadastro Geral";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("A situação FIM DE CESSÃO é somente para servidores cedidos que terminaram a cessão e não para celetistas");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorSemSexo
     * 
     * Servidor sem Sexo Cadastrado
     */
    public function get_servidorSemSexo($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Cadastro Geral";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                #callout("A situação FIM DE CESSÃO é somente para servidores cedidos que terminaram a cessão e não para celetistas");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorSemSexo
     * 
     * Servidor sem Sexo Cadastrado
     */
    public function get_servidorSemEstCiv($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Cadastro Geral";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                #callout("A situação FIM DE CESSÃO é somente para servidores cedidos que terminaram a cessão e não para celetistas");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorComAverbacaoAposAdmissao
     * 
     * Servidor sem Sexo Cadastrado
     */
    public function get_servidorComAverbacaoAposAdmissao($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = 1;
        $categoria = "Aposentadoria";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                #callout("A situação FIM DE CESSÃO é somente para servidores cedidos que terminaram a cessão e não para celetistas");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorComAverbacaoTerminandoAposAdmissao
     * 
     * Servidor sem Sexo Cadastrado
     */
    public function get_servidorComAverbacaoTerminandoAposAdmissao($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = 1;
        $categoria = "Aposentadoria";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                #callout("A situação FIM DE CESSÃO é somente para servidores cedidos que terminaram a cessão e não para celetistas");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorComDependentesSemParentesco
     * 
     * Servidor Com Dependentes (parentes) sem parentesco cadastrado
     */
    public function get_servidorComDependentesSemParentesco($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Cadastro Geral";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("É necessário definir o típo de parentesco entre o parente e o servidor");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorComTerminoReadaptacaoMenos90Dias
     * 
     * Servidor Com Readaptação terminando em menos de 90 dias
     */
    public function get_servidorComTerminoReadaptacaoMenos90Dias($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Benefícios";

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
                      AND TIMESTAMPDIFF(DAY,CURRENT_DATE,DATE_SUB(ADDDATE(tbreadaptacao.dtInicio, INTERVAL tbreadaptacao.periodo MONTH),INTERVAL 1 DAY)) <=90';
        if (!empty($idServidor)) {
            $select .= ' AND idServidor = "' . $idServidor . '"';
        }
        $select .= ' ORDER BY 7 desc';

        $result = $servidor->select($select);
        $count = $servidor->count($select);
        $titulo = 'Readaptação terminando em menos de 90 dias.';

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                #callout("A situação FIM DE CESSÃO é somente para servidores cedidos que terminaram a cessão e não para celetistas");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorComTerminoReducaoMenos90Dias
     * 
     * Servidor Com Redução terminando em menos de 90 dias
     */
    public function get_servidorComTerminoReducaoMenos90Dias($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Benefícios";

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
                      AND TIMESTAMPDIFF(DAY,CURRENT_DATE,DATE_SUB(ADDDATE(tbreducao.dtInicio, INTERVAL tbreducao.periodo MONTH),INTERVAL 1 DAY)) <=90';
        if (!empty($idServidor)) {
            $select .= ' AND idServidor = "' . $idServidor . '"';
        }
        $select .= ' ORDER BY 7 desc';

        $result = $servidor->select($select);
        $count = $servidor->count($select);
        $titulo = 'Redução da CH terminando em menos de 90 dias.';

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                #callout("A situação FIM DE CESSÃO é somente para servidores cedidos que terminaram a cessão e não para celetistas");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorComTerminoLicencaSemVencimentosMenos90Dias
     * 
     * Servidor Com Licença Sem Vencimentos terminando em menos de 90 dias
     */
    public function get_servidorComTerminoLicencaSemVencimentosMenos90Dias($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = null;
        $categoria = "Licenças";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                #callout("A situação FIM DE CESSÃO é somente para servidores cedidos que terminaram a cessão e não para celetistas");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorInativoComCargoEmComissao
     * 
     * Servidor Inativo com cargo em comissao
     */
    public function get_servidorInativoComCargoEmComissao($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = 1;
        $categoria = "Cargo em Comissão";

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("Deve-se exonerar o servidor do cargo em comissão antes de aposentar, demitir, exonerá-lo");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_servidorAtivoLicencaMedicaEmAberto
     * 
     * Servidor Com licença médica em aberto
     */
    public function get_servidorAtivoLicencaMedicaEmAberto($idServidor = null) {

        # Define a categoria e a prioridade
        $prioridade = 1;
        $categoria = "Licenças";

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
        $titulo = 'Servidor(es) com Licença Médica em Aberto (sem alta)';

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

        if ($count > 0) {
            if (!empty($idServidor)) {
                return $titulo;
            } elseif ($this->lista) {
                callout("Deve-se acertar as licenças médicas definindo se tem ou não alta");
                $tabela->show();
                set_session('alerta', $metodo[2]);
            } else {
                $retorna = [$count . ' ' . $titulo, $metodo[2], $categoria, $prioridade];
                return $retorna;
            }
        } elseif ($this->lista) {
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        }
    }

    ##########################################################
}
