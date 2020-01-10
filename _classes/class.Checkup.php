<?php

class Checkup {
 /**
  * Classe Checup
  * 
  * Faz um checup no banco de dados pessoal a procura de erros quanto ao banco de dados quanto as regras de negócio
  * 
  * By Alat
  */
	
    private $lista = TRUE;       // Informa se será listagem ou somente contagem dos registros
        
    ###########################################################

    /**
     * Método construct
     * 
     * Faz um checkup
     */
    
    public function __construct($lista = TRUE){
        $this->lista = $lista;
    }
    
    ###########################################################

    /**
     * Método get_all
     * 
     * Executa todos os métodos desta classe (menos é claro o get_all e o construct
     */
    
    public function get_all(){
        # Pega todos as informações da classe
        $api = new ReflectionClass($this);
        
        # Inicia um array para guardar o retorno 
        $metodoRetorno = array();
        
        # Percorre todos os métodos da classe e guarda no array seu retorno
        foreach($api->getMethods() as $method){
            if (($method->getName() <> 'get_all') AND ($method->getName() <> '__construct')){
                $metodo =  $method->getName();
                $metodoRetorno[] = $this->$metodo();
            }
        }
        
        # Ordena os métodos pela prioridade
        function cmp($a, $b) {          // Função específica que compara se $a é maior que $b
            return $a[2] > $b[2];
        }
        
        // Ordena
        usort($metodoRetorno, 'cmp');
        
        $prioridadeAnterior = NULL;
        
        # Percorre o array $metodoRetorno e exibe a lista
        foreach($metodoRetorno as $listaRetorno){
            
            # Exibe uma linha horizontal
            if($prioridadeAnterior <> $listaRetorno[2]){
                if(is_null($prioridadeAnterior)){
                    $prioridadeAnterior = $listaRetorno[2];
                }else{
                    $prioridadeAnterior = $listaRetorno[2];
                    hr("alerta");
                }
            }
            
            $link = new Link($listaRetorno[0],"?fase=alerta&alerta=".$listaRetorno[1]);
            $link->set_id("checkupResumo".$listaRetorno[2]);
            echo "<li>";
            $link->show();
            echo "</li>";
        }
    }
            
    ###########################################################

    /**
     * Método get_licencaVencendo
     * 
     * Servidores com Licença vencendo este ano
     */
    
    public function get_licencaVencendo($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 4;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
       
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
              AND YEAR(ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)) = "'.date('Y').'"';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY 7';

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $titulo = 'Servidor(es) com licença terminando em '.date('Y');
        $label = ['IdFuncional','Nome','Perfil','Licença','Data Inicial','Dias','Data Final'];
        $funcao = [NULL,NULL,NULL,NULL,"date_to_php",NULL,"date_to_php"];
        $align = ['center','left'];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
        
        if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_licencaPremioVencendo
     * 
     * Servidores com Licença Premio vencendo este ano
     */
    
    /*
    
    public function get_licencaPremioVencendo($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 4;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
       
        $select = 'SELECT tbservidor.idFuncional,
                  tbpessoa.nome,
                  tbperfil.nome,
                  tblicencapremio.dtInicial,
                  tblicencapremio.numDias,
                  ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1),
                  tbservidor.idServidor
             FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                             LEFT JOIN tblicencapremio USING (idServidor)
                             LEFT JOIN tbperfil USING (idPerfil)
            WHERE tbservidor.situacao = 1
              AND YEAR(ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1)) = "'.date('Y').'"';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY 7';

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $titulo = 'Servidor(es) com '.$servidor->get_licencaNome(6).' terminando em '.date('Y');
        $label = ['IdFuncional','Nome','Perfil','Data Inicial','Dias','Data Final'];
        $funcao = [NULL,NULL,NULL,"date_to_php",NULL,"date_to_php"];
        $align = ['center','left'];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
        
        if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }
     * 
     */

    ##########################################################

    /**
     * Método get_trienioVencendo
     * 
     * Servidores com trênio vencendo este ano
     */
    
    public function get_trienioVencendo($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 1;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
            
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
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' 
         GROUP BY tbservidor.idServidor
         HAVING YEAR (DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR)) = '.date('Y').'
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
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= '        
         GROUP BY tbservidor.idServidor
         HAVING YEAR (DATE_ADD(tbservidor.dtadmissao, INTERVAL 3 YEAR)) = '.date('Y').'
             AND MAX(tbtrienio.dtInicial) IS NULL
         ORDER BY 6)
         ORDER BY 6';		

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = ['IdFuncional','Nome','Admissão','Último Percentual','Último Triênio','Próximo Triênio'];
        $align = ['center','left'];
        $titulo = 'Servidor(es) com triênio vencendo em '.date('Y');
        $funcao = [NULL,NULL,"date_to_php",NULL,"date_to_php","date_to_php"];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
        
        if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
       }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_trienioVencido
     * 
     * Servidores com trênio vencido anterior a esse ano
     */
    
    public function get_trienioVencido($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 1;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        
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
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= '        
         GROUP BY tbservidor.idServidor
         HAVING YEAR (DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR)) < '.date('Y').'
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
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= '                      
         GROUP BY tbservidor.idServidor
         HAVING YEAR (DATE_ADD(tbservidor.dtadmissao, INTERVAL 3 YEAR)) < '.date('Y').'
             AND MAX(tbtrienio.dtInicial) IS NULL
         ORDER BY 6)
         ORDER BY 6';		

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = ['IdFuncional','Nome','Admissão','Último Percentual','Último Triênio','Deveriam ter recebido em:'];
        $align = ['center','left'];
        $titulo = 'Servidor(es) com triênio vencido antes de '.date('Y');
        $funcao = [NULL,NULL,"date_to_php",NULL,"date_to_php","date_to_php"];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
        
        if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_auxilioCrecheVencido
     * 
     * Servidores com o auxílio creche vencendo este ano
     */
    
    public function get_auxilioCrecheVencido($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 4;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        
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
              AND YEAR(dtTermino) = "'.date('Y').'"';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= '        
         ORDER BY dtTermino';

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $titulo = 'Servidor(es) com o auxílio creche vencendo em '.date('Y');
        $label = ["IdFuncional","Servidor","Dependente","Nascimento","Término do Aux.","CI Exclusão","Processo"];
        $funcao = [NULL,NULL,NULL,"date_to_php","date_to_php"];
        $align = ['center','left','left'];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
        
        if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_motoristaCarteiraVencida
     * 
     * Motoristas com carteira de habilitação vencida no sistema
     */
    
    public function get_motoristaCarteiraVencida($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 3;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);

        $select = 'SELECT tbservidor.idFuncional, 
                          tbservidor.matricula,  
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
       
        if(!is_null($idServidor)){
            $select .= ' AND idServidor = "'.$idServidor.'"';
        }

        $select .= ' ORDER BY tbpessoa.nome';		

        $result = $servidor->select($select);
        $count = $servidor->count($select);
        
        # Cabeçalho da tabela
        $label = ['IdFuncional','Matrícula','Nome','Habilitação','Data da Carteira','Cargo'];
        $align = ['center','center','left','center','center','left'];
        $titulo = 'Motorista(s) com carteira de habilitação vencida';
        $funcao = [NULL,"dv",NULL,NULL,"date_to_php"];
        $classe = [NULL,NULL,NULL,NULL,NULL,"Pessoal"];
        $rotina = [NULL,NULL,NULL,NULL,NULL,"get_cargo"];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
        
        if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                callout("Solicitar aos motoristas que compareçam a GRH com a cópia da carteira para ser arquivada.<br/>Lembre-se de cadastrar no sistema, na área de documentos do motorista, a nova data, senão esta mensagem continuará sendo exibida para esse servidor.");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_motoristaSemDataCarteira
     * 
     * Motoristas com carteira de habilitação sem data de vencimento cadastrada no sistema
     */
    
    public function get_motoristaSemDataCarteira($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 3;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);

       $select = 'SELECT tbservidor.idFuncional, 
                          tbservidor.matricula,  
                          tbpessoa.nome,
                          motorista,
                          tbdocumentacao.dtVencMotorista,
                          tbservidor.idServidor,
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idpessoa)
                                     LEFT JOIN tbdocumentacao USING (idpessoa)
                    WHERE tbservidor.situacao = 1
                    AND tbservidor.idcargo = 63
                    AND tbdocumentacao.dtVencMotorista is NULL';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }
        $select .= ' ORDER BY tbpessoa.nome';			

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = ['IdFuncional','Matrícula','Nome','Habilitação','Data da Carteira','Cargo'];
        $align = ['center','center','left','center','center','left'];
        $titulo = 'Motorista(s) com carteira de habilitação sem data de vencimento';
        $funcao = [NULL,"dv",NULL,NULL,"date_to_php"];
        $classe = [NULL,NULL,NULL,NULL,NULL,"Pessoal"];
        $rotina = [NULL,NULL,NULL,NULL,NULL,"get_cargo"];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
        
        if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                callout("Solicitar aos motoristas que compareçam a GRH com a cópia da carteira para ser arquivada. Lembre-se de cadastrar no sistema, na área de documentos do motorista, a data da carteira, senão esta mensagem continuará sendo exibida para esse servidor.");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
       }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_motoristaSemCarteira
     * 
     * Motorista sem número da carteira de habilitação cadastrada:
     */
    
    public function get_motoristaSemCarteira($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 3;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);

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
                    AND (tbdocumentacao.motorista is NULL OR tbdocumentacao.motorista ="")';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }
        $select .= ' ORDER BY tbpessoa.nome';			

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Matrícula','Nome','Habilitação','Cargo');
        $align = array('center','center','left');
        $titulo = 'Motorista(s) sem número da carteira de habilitação cadastrada:';
        $classe = array(NULL,NULL,NULL,NULL,"Pessoal");
        $rotina = array(NULL,NULL,NULL,NULL,"get_cargo");
        $funcao = array(NULL,"dv");
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
        
        if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                callout("Solicitar aos motoristas que compareçam a GRH com a cópia da carteira para ser arquivada. Lembre-se de cadastrar no sistema, na área de documentos do motorista, os dados da carteira de habilitação, senão esta mensagem continuará sendo exibida para esse servidor.");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorCom74
     * 
     * Servidor estatutário que faz 75 anos este ano (Preparar aposentadoria compulsória)
     */
    
    public function get_servidorCom74($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 3;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);

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
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }
        $select .= ' ORDER BY tbpessoa.nome';				

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Nome','Data de Nascimento','Idade','Lotação','Cargo');
        $align = array('center','left','center','center','left','left');
        $titulo = 'Servidor(es) estatutário(s) que faz 75 anos este ano. Preparar aposentadoria compulsória';
        $classe = array(NULL,NULL,NULL,NULL,"Pessoal","Pessoal");
        $rotina = array(NULL,NULL,NULL,NULL,"get_lotacao","get_cargo");
        $funcao = array(NULL,NULL,"date_to_php");
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                callout("Avisar ao servidor sobre a aposentadoria compulsória.");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorComMais74
     * 
     * Servidor estatutário com 75 anos ou mais (Aposentar Compulsoriamente)
     */
    
    public function get_servidorComMais75($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 1;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);

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
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }
        $select .= ' ORDER BY tbpessoa.nome';			

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Nome','Data de Nascimento','Idade','Lotação','Cargo');
        $align = array('center','left','center','center','left','left');
        $titulo = 'Servidor(es) estatutário com 75 anos ou mais. Aposentar Compulsoriamente';
        $classe = array(NULL,NULL,NULL,NULL,"Pessoal","Pessoal");
        $rotina = array(NULL,NULL,NULL,NULL,"get_lotacao","get_cargo");
        $funcao = array(NULL,NULL,"date_to_php");
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
       if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
       }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorComMaisde1MatriculaAtiva
     * 
     * Servidor estatutário com mais de uma matriculka ativa
     */
    
    public function get_servidorComMaisde1MatriculaAtiva($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 1;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);

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
        if(!is_null($idServidor)){
            $select .= ' AND idServidor = "'.$idServidor.'"';
        }
        
        $select .= ' ORDER BY tbpessoa.nome';

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Matrícula','Nome','Perfil','Lotação','Cargo','Situação');
        $align = array('center','center','left','center','left','left','center');
        $titulo = 'Servidor(es) com mais de uma matrícula ativa';
        $classe = array(NULL,NULL,NULL,NULL,"Pessoal","Pessoal");
        $rotina = array(NULL,NULL,NULL,NULL,"get_lotacao","get_cargo");
        #$funcao = array(NULL,NULL,"date_to_php");
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        #$tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
        
        if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                callout("Servidor com 2 matriculas Ativas !! Houve algum erro no sistema, favor verificar. Somente uma matrícula deveria estar ativa");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorComPerfilOutros
     * 
     * Servidor Ativo com perfil outros
     */
    
    public function get_servidorComPerfilOutros($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 1;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);

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
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }
        $select .= ' ORDER BY tbpessoa.nome';

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Matrícula','Nome','Perfil','Lotação','Cargo','Situação');
        $align = array('center','center','left','center','left','left','center');
        $titulo = 'Servidor(es) com perfil outros';
        $classe = array(NULL,NULL,NULL,NULL,"Pessoal","Pessoal");
        $rotina = array(NULL,NULL,NULL,NULL,"get_lotacao","get_cargo");
        #$funcao = array(NULL,NULL,"date_to_php");
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        #$tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                callout("O perfil outros foi definido na importação para servidores que estavam com perfil em branco. Deve-se analisar para saber o real perfil desse servidor ou se não for servidor efetuar sua exclusão do sistema.");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorSemPerfil
     * 
     * Servidor com perfil outros
     */
    
    public function get_servidorSemPerfil($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 1;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);

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
                    WHERE idPerfil is NULL
                      AND tbservidor.situacao = 1';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';
        
        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Matrícula','Nome','Perfil','Lotação','Cargo','Situação');
        $align = array('center','center','left','center','left','left','center');
        $titulo = 'Servidor(es) sem perfil cadastrado';
        $classe = array(NULL,NULL,NULL,NULL,"Pessoal","Pessoal");
        $rotina = array(NULL,NULL,NULL,NULL,"get_lotacao","get_cargo");
        #$funcao = array(NULL,NULL,"date_to_php");
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        #$tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                callout("Algum erro no sistema, favor verificar. Todos os servidores devem tem um perfil cadastrado.");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorTecnicoEstatutarioSemConcurso
     * 
     * Servidor Concursado sem concurso cadastrado
     */
    
    public function get_servidorTecnicoEstatutarioSemConcurso($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 3;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);

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
                    WHERE idConcurso is NULL
                      AND tbservidor.situacao = 1
                      AND idPerfil = 1
                      AND (idCargo <> 128 AND idCargo <> 129)';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY dtAdmissao,tbpessoa.nome';
                 

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = ['IdFuncional','Matrícula','Admissão','Nome','Perfil','Lotação','Cargo','Situação'];
        $align = ['center','center','center','left','center','left','left','center'];
        $titulo = 'Servidor(es) técnico(s) estatutário(s) sem concurso cadastrado';
        $classe = [NULL,NULL,NULL,NULL,NULL,"Pessoal","Pessoal"];
        $rotina = [NULL,NULL,NULL,NULL,NULL,"get_lotacao","get_cargo"];
        $funcao = [NULL,"dv","date_to_php"];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                callout("Todo servidor concursado deve ter cadastrado o concurso no qual foi aprovado.");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorProfessorAtivoSemConcurso
     * 
     * Servidor Concursado sem concurso cadastrado
     */
    
    public function get_servidorProfessorAtivoSemConcurso($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 3;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);

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
                    WHERE tbvagahistorico.idConcurso is NULL
                      AND tbservidor.situacao = 1
                      AND (idPerfil = 1 OR idPerfil = 4)
                      AND (idCargo = 128 OR idCargo = 129)';
                if(!is_null($idServidor)){
                    $select .= ' AND tbservidor.idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY dtAdmissao,tbpessoa.nome';

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = ['IdFuncional','Matrícula','Admissão','Nome','Perfil','Lotação','Cargo','Situação'];
        $align = ['center','center','center','left','center','left','left','center'];
        $titulo = 'Professores ativos sem concurso cadastrado';
        $classe = [NULL,NULL,NULL,NULL,NULL,"Pessoal","Pessoal"];
        $rotina = [NULL,NULL,NULL,NULL,NULL,"get_lotacao","get_cargo"];
        $funcao = [NULL,"dv","date_to_php"];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                callout("Todo servidor concursado deve ter cadastrado o concurso no qual foi aprovado.");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorProfessorAtivoSemConcurso
     * 
     * Servidor Concursado sem concurso cadastrado
     */
    
    public function get_servidorProfessorInativoSemConcurso($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 3;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);

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
                    WHERE tbvagahistorico.idConcurso is NULL
                      AND tbservidor.situacao <> 1
                      AND (idPerfil = 1 OR idPerfil = 4)
                      AND (idCargo = 128 OR idCargo = 129)';
                if(!is_null($idServidor)){
                    $select .= ' AND tbservidor.idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY dtAdmissao,tbpessoa.nome';

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = ['IdFuncional','Matrícula','Admissão','Nome','Perfil','Lotação','Cargo','Situação'];
        $align = ['center','center','center','left','center','left','left','center'];
        $titulo = 'Professores inativos sem concurso cadastrado';
        $classe = [NULL,NULL,NULL,NULL,NULL,"Pessoal","Pessoal"];
        $rotina = [NULL,NULL,NULL,NULL,NULL,"get_lotacao","get_cargo"];
        $funcao = [NULL,"dv","date_to_php"];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                callout("Todo servidor concursado deve ter cadastrado o concurso no qual foi aprovado.");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_cargoComissaoNomeacaoIgualExoneracao
     * 
     * Cargo em comissão nomeado e exonerado no mesmo dia?!
     */
    
    public function get_cargoComissaoNomeacaoIgualExoneracao($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 1;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);

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
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';
              

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Matrícula','Nome','Nomeação','Exoneração','Descrição');
        $align = array('center','center','left','center','center','left');
        $titulo = 'Cargo em comissão nomeado e exonerado no mesmo dia';
        $classe = array(NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,"CargoComisso");
        $rotina = array(NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,"get_descricaoCargo");
        $funcao = array(NULL,"dv",NULL,"date_to_php","date_to_php","descricaoComissao");
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        #$tabela->set_classe($classe);
        #$tabela->set_metodo($rotina);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                callout("Cargo em comissão nomeado e exonerado no mesmo dia.");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorSemIdFuncional
     * 
     * Exibe servidor ativo sem id Funcional cadastrado que não for bolsista
     */
    
    public function get_servidorSemIdFuncional($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 3;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        

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
                    WHERE (idFuncional IS NULL OR idFuncional = "")
                      AND idPerfil <> 10
                      AND tbservidor.situacao = 1';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';                 

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Matrícula','Nome','Perfil','Lotação','Cargo','Situação');
        $align = array('center','center','left','center','left','left','center');
        $titulo = 'Servidor(es) sem id funcional cadastrado no sistema';
        $classe = array(NULL,NULL,NULL,NULL,"Pessoal","Pessoal");
        $rotina = array(NULL,NULL,NULL,NULL,"get_lotacao","get_cargo");
        #$funcao = array(NULL,NULL,"date_to_php");
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        #$tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                #callout("Servidor sem Id Funcional cadastrado no Sistema");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorSemDtNasc
     * 
     * Servidor sem data de nasciment cadastrada
     */
    
    public function get_servidorSemDtNasc($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 3;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        
        $select = 'SELECT tbservidor.idFuncional,  
                          tbpessoa.nome,
                          dtNasc,
                          tbservidor.idServidor,
                          tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                    WHERE tbpessoa.dtNasc is NULL
                    AND situacao = 1';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';         		

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Nome','Data de Nascimento','Lotação','Cargo');
        $align = array('center','left','center','left','left');
        $titulo = 'Servidor(es) sem data de nascimento cadastrada no sistema';
        $classe = array(NULL,NULL,NULL,"Pessoal","Pessoal");
        $rotina = array(NULL,NULL,NULL,"get_lotacao","get_cargo");
        #$funcao = array(NULL,NULL,"date_to_php");
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        #$tabela->set_funcao($funcao);
        $tabela->set_metodo($rotina);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                callout("O cadastro da data de nascimento do servidor é necessário para diversas rotinas do sistema. Verifique se na pasta do arquivo não tem nenhuma cópia de documento que tenha essa informação.");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorCedidoLotacaoErrada
     * 
     * Servidor DA UENF cedido a outro orgão que não está lotado na reitoria cedidos
     */
    
    public function get_servidorCedidoLotacaoErrada($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 3;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        
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
                      AND (isNull(tbhistcessao.dtFim) OR current_date() <= tbhistcessao.dtFim)
                      AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                      AND situacao = 1
                      AND tbhistlot.lotacao <> 113';
                if(!is_null($idServidor)){
                    $select .= ' AND tbservidor.idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome'; 
        
        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Nome','Órgão','Início','Término','Lotação');
        $align = array('center','left','left','center','center','left');
        $titulo = 'Servidor(es) cedido(s) pela UENF sem estar lotado no Reitoria - Cedidos';
        $classe = array(NULL,NULL,NULL,NULL,NULL,"Pessoal");
        $rotina = array(NULL,NULL,NULL,NULL,NULL,"get_lotacao");
        $funcao = array(NULL,NULL,NULL,"date_to_php","date_to_php");
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_funcao($funcao);
        $tabela->set_metodo($rotina);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                callout("O servidor cedido pela UENF deve estar cadastrado no setor Reitoria - Cessão.");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorCedidoDataExpirada
     * 
     * Servidor DA UENF cedido a outro orgão onde a dta de término de cassão já passou mas continua cedido na reitoria cedidos
     */
    
    public function get_servidorCedidoDataExpirada($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 3;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        
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
                if(!is_null($idServidor)){
                    $select .= ' AND tbservidor.idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome'; 
        
        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Nome','Órgão','Início','Término','Lotação');
        $align = array('center','left','left','center','center','left');
        $titulo = 'Servidor(es) cedido(s) pela UENF que terminaram a cessão mas ainda lotados na Reitoria - Cedidos';
        $classe = array(NULL,NULL,NULL,NULL,NULL,"Pessoal");
        $rotina = array(NULL,NULL,NULL,NULL,NULL,"get_lotacao");
        $funcao = array(NULL,NULL,NULL,"date_to_php","date_to_php");
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_funcao($funcao);
        $tabela->set_metodo($rotina);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                callout("Os servidores cedidos pela UENF que já terminaram o período de cessão deverão ser (re)lotados na universidade ou devem ter seu período de cessão renovado.");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorEstatutarioSemCargo
     * 
     * Servidor estatutário sem cargo cadastrado:
     */
    
    public function get_servidorEstatutarioSemCargo($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 1;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        

        $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,        
                          idServidor,
                          tbperfil.nome,   
                          idServidor,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                    WHERE (idCargo IS NULL OR idCargo = 0)
                      AND situacao = 1
                      AND idPerfil = 1';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';                 

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = ['IdFuncional','Matrícula','Nome','Lotação','Perfil','Cargo'];
        $align = ['center','center','left','left','center'];
        $titulo = 'Servidor(es) estatutário(s) sem cargo cadastrado.';
        $classe = [NULL,NULL,NULL,"Pessoal",NULL,"Pessoal"];
        $rotina = [NULL,NULL,NULL,"get_lotacao",NULL,"get_cargo"];
        $funcao = [NULL,"dv"];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                #callout("Servidor sem Id Funcional cadastrado no Sistema");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorSemCargo
     * 
     * Servidor NÃO estatutário E NÃO bolsista sem cargo cadastrado:
     */
    
    public function get_servidorSemCargo($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 3;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        

        $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                    WHERE (idCargo IS NULL OR idCargo = 0)
                      AND situacao = 1
                      AND idPerfil <> 10
                      AND idPerfil <> 1';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';                 

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Matrícula','Nome','Perfil','Lotação','Cargo');
        $align = array('center','center','left','center','left');
        $titulo = 'Servidor(es) sem cargo cadastrado.';
        $classe = array(NULL,NULL,NULL,NULL,"Pessoal","Pessoal");
        $rotina = array(NULL,NULL,NULL,NULL,"get_lotacao","get_cargo");
        $funcao = array(NULL,"dv");
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                #callout("Servidor sem Id Funcional cadastrado no Sistema");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorCedidoSemInfoCedente
     * 
     * Servidor cedido PARA a UENF sem informação do órgão cedente
     */
    
    public function get_servidorCedidoSemInfoCedente($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 1;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        
        $select = 'SELECT tbservidor.idFuncional,  
                          tbpessoa.nome,
                          tbcedido.orgaoOrigem,
                          tbservidor.idServidor,
                          tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbcedido USING (idServidor)
                                         JOIN tbpessoa USING (idPessoa)
                    WHERE (tbcedido.orgaoOrigem is NULL
                       OR tbcedido.orgaoOrigem = "")
                      AND situacao = 1
                      AND idPerfil = 2';
                if(!is_null($idServidor)){
                    $select .= ' AND tbservidor.idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome'; 
        
        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Nome','Órgão Cedente','Lotação');
        $align = array('center','left','left','left');
        $titulo = 'Servidor(es) cedido(s) para UENF sem informações da cessão';
        $classe = array(NULL,NULL,NULL,"Pessoal");
        $rotina = array(NULL,NULL,NULL,"get_lotacao");
        #$funcao = array(NULL,NULL,NULL,"date_to_php","date_to_php");
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        #$tabela->set_funcao($funcao);
        $tabela->set_metodo($rotina);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                callout("O servidor cedido psra a UENF deve ter cadastrado as informações da cessão.");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorInativoComPerfilOutros
     * 
     * Servidor Inativo com perfil outros
     */
    
    public function get_servidorInativoComPerfilOutros($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 3;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);

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
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }
        $select .= ' ORDER BY tbpessoa.nome';

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Matrícula','Nome','Perfil','Lotação','Cargo','Situação');
        $align = array('center','center','left','center','left','left','center');
        $titulo = 'Servidor(es) inativo(s) com perfil outros';
        $classe = array(NULL,NULL,NULL,NULL,"Pessoal","Pessoal");
        $rotina = array(NULL,NULL,NULL,NULL,"get_lotacao","get_cargo");
        $funcao = array(NULL,"dv");
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                callout("O perfil outros foi definido na importação para servidores que estavam com perfil em branco.<br/>Deve-se analisar para saber o real perfil desse servidor ou se não for servidor efetuar sua exclusão do sistema.");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorInativoSemMotivoSaida
     * 
     * Servidor inativo sem motivo de saída:
     */
    
    public function get_servidorInativoSemMotivoSaida($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 3;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        

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
                      AND (motivo is NULL OR motivo = 0)';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';                 

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = ['IdFuncional','Matrícula','Nome','Perfil','Cargo','Situação','Motivo'];
        $align = ['center','center','left','center','left'];
        $titulo = 'Servidor(es) inativo(s) sem motivo de saída cadastrado.';
        $classe = [NULL,NULL,NULL,NULL,"Pessoal","Pessoal"];
        $rotina = [NULL,NULL,NULL,NULL,"get_cargo","get_situacao"];
        $funcao = [NULL,"dv"];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                #callout("Servidor sem Id Funcional cadastrado no Sistema");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorInativoSemdataSaida
     * 
     * Servidor inativo sem data de saída:
     */
    
    public function get_servidorInativoSemdataSaida($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 3;
                
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        

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
                      AND (dtDemissao IS NULL OR dtDemissao = "")';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';                 

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = ['IdFuncional','Matrícula','Nome','Perfil','Cargo','Situação','Saída'];
        $align = ['center','center','left','center','left'];
        $titulo = 'Servidor(es) inativo(s) sem data de saída cadastrada.';
        $classe = [NULL,NULL,NULL,NULL,"Pessoal","Pessoal"];
        $rotina = [NULL,NULL,NULL,NULL,"get_cargo","get_situacao"];
        $funcao = [NULL,"dv"];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                #callout("Servidor sem Id Funcional cadastrado no Sistema");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorDuplicado
     * 
     * Servidor Duplicado no Sistema
     */
    
    public function get_servidorDuplicado($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 1;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        

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
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';                 

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Matrícula','Nome','Perfil','Lotação');
        $align = array('center','center','left','center','left');
        $titulo = 'Servidor(es) duplicado(s) no sistema.';
        $classe = array(NULL,NULL,NULL,NULL,"Pessoal");
        $rotina = array(NULL,NULL,NULL,NULL,"get_lotacao");
        #$funcao = array(NULL,NULL,"date_to_php");
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        #$tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                callout("Verifique se não existem 2 lançamentos de lotação com o mesmo dia. Isso gera registros duplos em listagem onde é exibidda a lotação do servidor.");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorSemSituacao
     * 
     * Servidor sem situação cadastrada
     */
    
    public function get_servidorSemSituacao($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 1;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        

        $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          idServidor,                          
                          idServidor,
                          idServidor,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)                                     
                    WHERE situacao IS NULL OR situacao > 6';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';                 

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Matrícula','Nome','Perfil','Cargo','Situação');
        $align = array('center','center','left','center','left');
        $titulo = 'Servidor(es) sem situacao cadastrada.';
        $classe = array(NULL,NULL,NULL,"Pessoal","Pessoal","Pessoal");
        $rotina = array(NULL,NULL,NULL,"get_perfil","get_cargo","get_situacao");
        #$funcao = array(NULL,NULL,"date_to_php");
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        #$tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                #callout("Servidor sem Id Funcional cadastrado no Sistema");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorSemAdmissao
     * 
     * Servidor sem data de admissão
     */
    
    public function get_servidorSemAdmissao($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 1;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        

        $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          dtAdmissao
                          idServidor,                          
                          idServidor,
                          idServidor,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)                                     
                    WHERE dtadmissao IS NULL';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';                 

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Matrícula','Nome','Admissão','Perfil','Cargo','Situação');
        $align = array('center','center','left','center','center','left');
        $titulo = 'Servidor(es) sem data de admissão cadastrada.';
        $classe = array(NULL,NULL,NULL,NULL,"Pessoal","Pessoal","Pessoal");
        $rotina = array(NULL,NULL,NULL,NULL,"get_perfil","get_cargo","get_situacao");
        #$funcao = array(NULL,NULL,"date_to_php");
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        #$tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                #callout("Servidor sem Id Funcional cadastrado no Sistema");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorSemProcessoPremio
     * 
     * Servidor estatutario ativo sem processo de Licença Premio (especial) 
     */
    
    public function get_servidorSemProcessoPremio($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 4;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        

        $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          idServidor,                          
                          idServidor,
                          idServidor,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)                                     
                    WHERE processoPremio IS NULL
                      AND idPerfil = 1
                      AND situacao = 1';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';                 

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Matrícula','Nome','Perfil','Cargo','Situação');
        $align = array('center','center','left','center','left');
        $titulo = 'Servidor(es) estatutário(s) sem processo de '.$servidor->get_licencaNome(6);
        $classe = array(NULL,NULL,NULL,"Pessoal","Pessoal","Pessoal");
        $rotina = array(NULL,NULL,NULL,"get_perfil","get_cargo","get_situacao");
        #$funcao = array(NULL,NULL,"date_to_php");
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        #$tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                #callout("Servidor sem Id Funcional cadastrado no Sistema");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_feriasAntesAdmissao
     * 
     * Servidores com Férias anteriores a data de admissão
     */
    
    public function get_feriasAntesAdmissao($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 1;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
       
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
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY 2,4 desc';

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $titulo = 'Férias anteriores a data de Admissão do servidor';
        $label = ['IdFuncional','Nome','Perfil','Ano Exercicio','Data Inicial','Dias','Admissão'];
        $funcao = [NULL,NULL,NULL,NULL,"date_to_php",NULL,"date_to_php"];
        $align = ['center','left'];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
        
        if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_licencaPremioEstranha
     * 
     * Servidores com Licença Prêmio com dias diferente de 30, 60 e 90 dias
     */
    
    public function get_licencaPremioEstranha($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 1;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
       
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
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $titulo = 'Servidores com Licença Prêmio diferente de 30,60 e 90 dias';
        $label = ['IdFuncional','Nome','Cargo','Lotação','Dias'];
        $classe = array(NULL,NULL,"Pessoal","Pessoal");
        $rotina = array(NULL,NULL,"get_cargo","get_lotacao");
        $align = ['center','left'];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
        
        if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                callout("A licença prêmio deve ter 30, 60 ou 90 dias. Valores diferentes podem ter sido causados na importação dos dados onde outro tipo de licença foi atribuido, erroneamente, como licença prêmio.");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_estatutarioComLicencaMedicaClt
     * 
     * Servidor estatutario ativo com licença medica CLT
     */
    
    public function get_estatutarioComLicencaMedicaClt($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 3;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        
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
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';                 

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Matrícula','Nome','Alta','Data Inicial','Dias','Data Final');
        $align = array('center','center','left','center','left');
        $titulo = 'Servidor(es) estatutário(s) com licença medica CLT';
        #$classe = array(NULL,NULL,NULL,"Pessoal","Pessoal","Pessoal");
        #$rotina = array(NULL,NULL,NULL,"get_perfil","get_cargo","get_situacao");
        #$funcao = array(NULL,NULL,"date_to_php");
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        #$tabela->set_classe($classe);
        #$tabela->set_metodo($rotina);
        #$tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                #callout("Servidor sem Id Funcional cadastrado no Sistema");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_folgaFruidaTreMaiorConcedida
     * 
     * Servidores com Mais folgas fruídas do que concedidas
     */
    
    public function get_folgaFruidaTreMaiorConcedida($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 3;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
       
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
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY 2,4 desc';

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $titulo = 'Servidor(es) com mais folgas fruídas do Tre do que concedidas';
        $label = ['IdFuncional','Nome','Perfil','Lotação','Folgas Concedidas','Folgas Fruídas'];
        $funcao = [NULL];
        $classe = [NULL,NULL,NULL,"Pessoal","Pessoal","Pessoal"];
        $rotina = [NULL,NULL,NULL,"get_lotacao","get_treFolgasConcedidas","get_treFolgasFruidas"];
        $align = ['center','left'];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_funcao($funcao);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
        
        if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################

    /**
     * Método get_progressaoImportada
     * 
     * Servidores com progressão e/ou 
     */
    
    public function get_progressaoImportada($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 1;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
       
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
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $titulo = 'Servidor(es) ativos com progressão importada';
        $label = ['IdFuncional','Nome','Perfil','Lotação','Situação'];
        $funcao = [NULL];
        $classe = [NULL,NULL,NULL,"Pessoal","Pessoal"];
        $rotina = [NULL,NULL,NULL,"get_lotacao","get_situacao"];
        $align = ['center','left'];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_funcao($funcao);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
        
        if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

     ##########################################################

    /**
     * Método get_progressaoImportada
     * 
     * Servidores com progressão e/ou 
     */
    
    public function get_progressaoImportadaInativos($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 1;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
       
        $select = 'SELECT distinct tbservidor.idFuncional,
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
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $titulo = 'Servidor(es) INATIVOS com progressão importada';
        $label = ['IdFuncional','Nome','Perfil','Lotação','Situação'];
        $funcao = [NULL];
        $classe = [NULL,NULL,NULL,"Pessoal","Pessoal"];
        $rotina = [NULL,NULL,NULL,"get_lotacao","get_situacao"];
        $align = ['center','left'];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_funcao($funcao);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
        
        if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_celetistaInativoFimCessao
     * 
     * Celetista com situação Fim de Cessão
     */
    
    public function get_celetistaInativoFimCessao($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 3;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        

        $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)                                     
                    WHERE situacao = 6 AND idPerfil = 4';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';                 

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $titulo = 'Celetista(s) com situação Fim de Cessão.';
        $label = ['IdFuncional','Matrícula','Nome','Perfil','Cargo','Situação'];
        $align = ['center','center','left','center','left'];
        $classe = [NULL,NULL,NULL,NULL,"Pessoal","Pessoal"];
        $rotina = [NULL,NULL,NULL,NULL,"get_cargo","get_situacao"];
        $funcao = [NULL,"dv"];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                callout("A situação FIM DE CESSÃO é somente para servidores cedidos que terminaram a cessão e não para celetistas");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorSemSexo
     * 
     * Servidor sem Sexo Cadastrado
     */
    
    public function get_servidorSemSexo($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 3;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        

        $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)                                     
                    WHERE sexo is NULL';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';                 

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $titulo = 'Servidor sem sexo cadastrado no sistema.';
        $label = ['IdFuncional','Matrícula','Nome','Perfil','Cargo','Situação'];
        $align = ['center','center','left','center','left'];
        $classe = [NULL,NULL,NULL,NULL,"Pessoal","Pessoal"];
        $rotina = [NULL,NULL,NULL,NULL,"get_cargo","get_situacao"];
        $funcao = [NULL,"dv"];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                #callout("A situação FIM DE CESSÃO é somente para servidores cedidos que terminaram a cessão e não para celetistas");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }
        }elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorSemSexo
     * 
     * Servidor sem Sexo Cadastrado
     */
    
    public function get_servidorSemEstCiv($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 3;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        

        $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)                                     
                    WHERE estciv is NULL';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';                 

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $titulo = 'Servidor sem estado civil cadastrado no sistema.';
        $label = ['IdFuncional','Matrícula','Nome','Perfil','Cargo','Situação'];
        $align = ['center','center','left','center','left'];
        $classe = [NULL,NULL,NULL,NULL,"Pessoal","Pessoal"];
        $rotina = [NULL,NULL,NULL,NULL,"get_cargo","get_situacao"];
        $funcao = [NULL,"dv"];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                #callout("A situação FIM DE CESSÃO é somente para servidores cedidos que terminaram a cessão e não para celetistas");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }}elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorComAverbacaoAposAdmissao
     * 
     * Servidor sem Sexo Cadastrado
     */
    
    public function get_servidorComAverbacaoAposAdmissao($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 1;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        

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
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';                 

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $titulo = 'Servidor(es) com tempo averbado iniciando após admissão.';
        $label = ['IdFuncional','Matrícula','Nome','Perfil','Cargo','Situação','Admissão','Data da Averbação'];
        $align = ['center','center','left','center','left'];
        $classe = [NULL,NULL,NULL,NULL,"Pessoal","Pessoal"];
        $rotina = [NULL,NULL,NULL,NULL,"get_cargo","get_situacao"];
        $funcao = [NULL,"dv",NULL,NULL,NULL,NULL,"date_to_php","date_to_php"];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                #callout("A situação FIM DE CESSÃO é somente para servidores cedidos que terminaram a cessão e não para celetistas");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }}elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorComAverbacaoTerminandoAposAdmissao
     * 
     * Servidor sem Sexo Cadastrado
     */
    
    public function get_servidorComAverbacaoTerminandoAposAdmissao($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 1;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        

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
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';                 

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $titulo = 'Servidor(es) com tempo averbado terminando após admissão.';
        $label = ['IdFuncional','Matrícula','Nome','Perfil','Cargo','Situação','Admissão','Data Final da Averbação'];
        $align = ['center','center','left','center','left'];
        $classe = [NULL,NULL,NULL,NULL,"Pessoal","Pessoal"];
        $rotina = [NULL,NULL,NULL,NULL,"get_cargo","get_situacao"];
        $funcao = [NULL,"dv",NULL,NULL,NULL,NULL,"date_to_php","date_to_php"];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                #callout("A situação FIM DE CESSÃO é somente para servidores cedidos que terminaram a cessão e não para celetistas");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }}elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorComReducaoMenor90Dias
     * 
     * Servidor Com redução da carga horário terminando em menos de 90 dias
     */
    /*
    public function get_servidorComReducaoMenor90Dias($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 1;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        

        $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          idServidor,
                          idServidor,
                          tbreducao.dtInicio,
                          ADDDATE(tbreducao.dtInicio, INTERVAL tbreducao.periodo MONTH),
                          DATEDIFF(ADDDATE(tbreducao.dtInicio, INTERVAL tbreducao.periodo MONTH),curdate())
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                                     LEFT JOIN tbreducao USING (idServidor)
                    WHERE (
                           (DATEDIFF(ADDDATE(tbreducao.dtInicio, INTERVAL tbreducao.periodo MONTH),curdate()) > 0) 
                       AND (DATEDIFF(ADDDATE(tbreducao.dtInicio, INTERVAL tbreducao.periodo MONTH),curdate()) < 90)
                          )';
        
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';                 

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $titulo = 'Servidor(es) com redução da carga horária terminando em menos de 90 dias.';
        $label = ['IdFuncional','Matrícula','Nome','Perfil','Cargo','Situação','Data inicial','Data Final','Faltando'];
        $align = ['center','center','left','center','left'];
        $classe = [NULL,NULL,NULL,NULL,"Pessoal","Pessoal"];
        $rotina = [NULL,NULL,NULL,NULL,"get_cargo","get_situacao"];
        $funcao = [NULL,"dv",NULL,NULL,NULL,NULL,"date_to_php","date_to_php"];
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                #callout("A situação FIM DE CESSÃO é somente para servidores cedidos que terminaram a cessão e não para celetistas");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }}elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }
*/
    ##########################################################
    
     /**
     * Método get_servidorComDependentesSemParentesco
     * 
     * Servidor Com Dependentes (parentes) sem parentesco cadastrado
     */
    
    public function get_servidorComDependentesSemParentesco($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 3;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        

        $select = 'SELECT tbdependente.nome,
                          tbparentesco.Parentesco,
                          idfuncional,
                          matricula,
                          tbpessoa.nome,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)                                     
                                     JOIN tbdependente USING (idPessoa)
                                     LEFT JOIN tbparentesco ON (tbparentesco.idParentesco = tbdependente.parentesco)
                    WHERE tbdependente.parentesco IS NULL ';
        
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDEr BY tbpessoa.nome';                 

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $titulo = 'Servidor(es) com parentes sem parentesco cadastrado.';
        $label = ['Parente','Parentesco','ID Funcional','Matrícula','Servidor'];
        $align = ['left','center','center','center','left'];
        #$classe = [NULL,NULL,NULL,NULL,"Pessoal","Pessoal"];
        #$rotina = [NULL,NULL,NULL,NULL,"get_cargo","get_situacao"];
        #$funcao = [NULL,"dv",NULL,NULL,NULL,NULL,"date_to_php","date_to_php"];
        $linkEditar = 'servidor.php?fase=editar&id=';
        
        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        #$tabela->set_classe($classe);
        #$tabela->set_metodo($rotina);
        #$tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                #callout("A situação FIM DE CESSÃO é somente para servidores cedidos que terminaram a cessão e não para celetistas");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }}elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorComTerminoReadaptacaoMenos90Dias
     * 
     * Servidor Com Readaptação terminando em menos de 90 dias
     */
    
    public function get_servidorComTerminoReadaptacaoMenos90Dias($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 2;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        

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
                    WHERE tbreadaptacao.dtInicio IS NOT NULL
                      AND TIMESTAMPDIFF(DAY,CURRENT_DATE,DATE_SUB(ADDDATE(tbreadaptacao.dtInicio, INTERVAL tbreadaptacao.periodo MONTH),INTERVAL 1 DAY)) >= 0 
                      AND TIMESTAMPDIFF(DAY,CURRENT_DATE,DATE_SUB(ADDDATE(tbreadaptacao.dtInicio, INTERVAL tbreadaptacao.periodo MONTH),INTERVAL 1 DAY)) <=90';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY 7 desc';                 

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $titulo = 'Readaptação terminando em menos de 90 dias.';
        $label = ['IdFuncional','Matrícula','Nome','Perfil','Cargo','Lotação','Data Final','Dias Faltantes'];
        $align = ['center','center','left','center','left'];
        $classe = [NULL,NULL,NULL,NULL,"Pessoal","Pessoal"];
        $rotina = [NULL,NULL,NULL,NULL,"get_cargo","get_lotacao"];
        $funcao = [NULL,"dv",NULL,NULL,NULL,NULL,"date_to_php"];
        $linkEditar = 'servidor.php?fase=editar&id=';
        
        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                #callout("A situação FIM DE CESSÃO é somente para servidores cedidos que terminaram a cessão e não para celetistas");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }}elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorComTerminoReducaoMenos90Dias
     * 
     * Servidor Com Redução terminando em menos de 90 dias
     */
    
    public function get_servidorComTerminoReducaoMenos90Dias($idServidor = NULL){
        # Define a prioridade (1, 2 ou 3)
        $prioridade = 2;
        
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        

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
                    WHERE tbreducao.dtInicio IS NOT NULL
                      AND TIMESTAMPDIFF(DAY,CURRENT_DATE,DATE_SUB(ADDDATE(tbreducao.dtInicio, INTERVAL tbreducao.periodo MONTH),INTERVAL 1 DAY)) >= 0 
                      AND TIMESTAMPDIFF(DAY,CURRENT_DATE,DATE_SUB(ADDDATE(tbreducao.dtInicio, INTERVAL tbreducao.periodo MONTH),INTERVAL 1 DAY)) <=90';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY 7 desc';                 

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $titulo = 'Redução da CH terminando em menos de 90 dias.';
        $label = ['IdFuncional','Matrícula','Nome','Perfil','Cargo','Lotação','Data Final','Dias Faltantes'];
        $align = ['center','center','left','center','left'];
        $classe = [NULL,NULL,NULL,NULL,"Pessoal","Pessoal"];
        $rotina = [NULL,NULL,NULL,NULL,"get_cargo","get_lotacao"];
        $funcao = [NULL,"dv",NULL,NULL,NULL,NULL,"date_to_php"];
        $linkEditar = 'servidor.php?fase=editar&id=';
        
        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_label($label);
        $tabela->set_align($align);
        $tabela->set_titulo($titulo);
        $tabela->set_classe($classe);
        $tabela->set_metodo($rotina);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                #callout("A situação FIM DE CESSÃO é somente para servidores cedidos que terminaram a cessão e não para celetistas");
                $tabela->show();
                set_session('alerta',$metodo[2]);
            }else{
                $retorna = [$count.' '.$titulo,$metodo[2],$prioridade];
                return $retorna;
            }}elseif($this->lista){
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }
    }

    ##########################################################
}
