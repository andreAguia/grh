<?php

class Checkup
{
 /**
  * Classe Checup
  * 
  * Faz um checup no banco de dados pessoal a procura de erros quanto ao banco de dados quanto as regras de negócio
  * 
  * By Alat
  */
	
    private $lista = TRUE;       //informa se será listagem ou somente contagem dos registros
        
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
     * Executa todos os métodos desta classe (menos é claro o get_all eo construct
     */
    
    public function get_all(){
        $api = new ReflectionClass($this);
        foreach($api->getMethods() as $method){
            if (($method->getName() <> 'get_all') AND ($method->getName() <> '__construct')){
                $metodo =  $method->getName();
                $this->$metodo();
            }
        }
    }
            
    ###########################################################

    /**
     * Método get_licencaVencendo
     * 
     * Servidores com Licença vencendo este ano
     */
    
    public function get_licencaVencendo(){
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
              AND YEAR(ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)) = "'.date('Y').'"
              ORDER BY 7';

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $titulo = 'Servidores com licença terminando em '.date('Y');
        $label = array('IdFuncional','Nome','Perfil','Licença','Data Inicial','Dias','Data Final');
        $funcao = array(NULL,NULL,NULL,NULL,"date_to_php",NULL,"date_to_php");
        $align = array('center','left');
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
            if($this->lista){
                $tabela->show();
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }

    ###########################################################

    /**
     * Método get_trienioVencendo
     * 
     * Servidores com trênio vencendo este ano
     */
    
    public function get_trienioVencendo()
    {
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
              AND idPerfil = 1
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
              AND idPerfil = 1              
         GROUP BY tbservidor.idServidor
         HAVING YEAR (DATE_ADD(tbservidor.dtadmissao, INTERVAL 3 YEAR)) = '.date('Y').'
             AND MAX(tbtrienio.dtInicial) IS NULL
         ORDER BY 6)
         ORDER BY 6';		

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Nome','Admissão','Último Percentual','Último Triênio','Próximo Triênio');
        $align = array('center','left');
        $titulo = 'Servidores com triênio vencendo em '.date('Y');
        $funcao = array(NULL,NULL,"date_to_php",NULL,"date_to_php","date_to_php");
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
            if($this->lista){
                $tabela->show();
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }
    
    ###########################################################

    /**
     * Método get_trienioVencido
     * 
     * Servidores com trênio vencido anterior a esse ano
     */
    
    public function get_trienioVencido()
    {
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
              AND idPerfil = 1
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
              AND idPerfil = 1              
         GROUP BY tbservidor.idServidor
         HAVING YEAR (DATE_ADD(tbservidor.dtadmissao, INTERVAL 3 YEAR)) < '.date('Y').'
             AND MAX(tbtrienio.dtInicial) IS NULL
         ORDER BY 6)
         ORDER BY 6';		

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Nome','Admissão','Último Percentual','Último Triênio','Deveriam ter recebido em:');
        $align = array('center','left');
        $titulo = 'Servidores com triênio vencido antes de '.date('Y');
        $funcao = array(NULL,NULL,"date_to_php",NULL,"date_to_php","date_to_php");
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
            if($this->lista){
                $tabela->show();
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }

    ###########################################################

    /**
     * Método get_auxilioCrecheVencido
     * 
     * Servidores com o auxílio creche vencendo este ano
     */
    
    public function get_auxilioCrecheVencido()
    {
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
              AND YEAR(dtTermino) = "'.date('Y').'"
         ORDER BY dtTermino';

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $titulo = 'Servidores com o auxílio creche vencendo em '.date('Y');
        $label = array("IdFuncional","Servidor","Dependente","Nascimento","Término do Aux.","CI Exclusão","Processo");
        $funcao = array(NULL,NULL,NULL,"date_to_php","date_to_php");
        $align = array('center','left','left');
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
            if($this->lista){
                $tabela->show();
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }

    ###########################################################
    
     /**
     * Método get_motoristaCarteiraVencida
     * 
     * Motoristas com carteira de habilitação vencida no sistema
     */
    
    public function get_motoristaCarteiraVencida($idServidor = NULL)
    {
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);

        $select = 'SELECT tbservidor.idFuncional,  
                          tbpessoa.nome,
                          tbdocumentacao.dtVencMotorista,
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
        $label = array('IdFuncional','Nome','Data da Carteira');
        $align = array('center','left');
        $titulo = 'Motorista com carteira de habilitação vencida';
        $funcao = array(NULL,NULL,"date_to_php");
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
                callout("Solicitar aos motoristas que compareçam a GRH com a cópia da carteira para ser arquivada. Lembre-se de cadastrar no sistema, na área de documentos do motorista, a nova data, senão esta mensagem continuará sendo exibida para esse servidor.");
                $tabela->show();
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }
    
    ###########################################################
    
     /**
     * Método get_motoristaSemDataCarteira
     * 
     * Motoristas com carteira de habilitação sem data de vencimento cadastrada no sistema
     */
    
    public function get_motoristaSemDataCarteira($idServidor = NULL)
    {
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);

        $select = 'SELECT tbservidor.idFuncional,  
                          tbpessoa.nome,
                          tbdocumentacao.dtVencMotorista,
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
        $label = array('IdFuncional','Nome','Data da Carteira');
        $align = array('center','left');
        $titulo = 'Motorista com carteira de habilitação sem data de vencimento';
        $funcao = array(NULL,NULL,"date_to_php");
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
                callout("Solicitar aos motoristas que compareçam a GRH com a cópia da carteira para ser arquivada. Lembre-se de cadastrar no sistema, na área de documentos do motorista, a data da carteira, senão esta mensagem continuará sendo exibida para esse servidor.");
                $tabela->show();
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }
    
    ###########################################################
    
     /**
     * Método get_motoristaSemCarteira
     * 
     * Motorista sem número da carteira de habilitação cadastrada:
     */
    
    public function get_motoristaSemCarteira($idServidor = NULL)
    {
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);

        $select = 'SELECT tbservidor.idFuncional,  
                          tbpessoa.nome,
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
        $label = array('IdFuncional','Nome');
        $align = array('center','left');
        $titulo = 'Motorista sem número da carteira de habilitação cadastrada:';
        $funcao = array(NULL);
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
                callout("Solicitar aos motoristas que compareçam a GRH com a cópia da carteira para ser arquivada. Lembre-se de cadastrar no sistema, na área de documentos do motorista, os dados da carteira de habilitação, senão esta mensagem continuará sendo exibida para esse servidor.");
                $tabela->show();
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }
    
    ###########################################################
    
     /**
     * Método get_servidorCom74
     * 
     * Servidor estatutário que faz 75 anos este ano (Preparar aposentadoria compulsória)
     */
    
    public function get_servidorCom74($idServidor = NULL)
    {
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
        $titulo = 'Servidor estatutário que faz 75 anos este ano. Preparar aposentadoria compulsória';
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
        $tabela->set_funcao($funcao);
        $tabela->set_metodo($rotina);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('idServidor');
       
        if ($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                callout("Avisar ao servidor sobre a aposentadoria compulsória.");
                $tabela->show();
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }
    
    ###########################################################
    
     /**
     * Método get_servidorComMais74
     * 
     * Servidor estatutário com 75 anos ou mais (Aposentar Compulsoriamente)
     */
    
    public function get_servidorComMais75($idServidor = NULL)
    {
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
        $titulo = 'Servidor estatutário com 75 anos ou mais. Aposentar Compulsoriamente';
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
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }

    ###########################################################
    
     /**
     * Método get_servidorComMaisde1MatriculaAtiva
     * 
     * Servidor estatutário com 75 anos ou mais (Aposentar Compulsoriamente)
     */
    
    public function get_servidorComMaisde1MatriculaAtiva()
    {
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
                    WHERE idPessoa IN (SELECT idpessoa FROM tbservidor WHERE tbservidor.situacao = 1 GROUP BY idPessoa HAVING COUNT(*) > 1 ORDER BY idpessoa)
                      AND tbservidor.situacao = 1
                 ORDER BY tbpessoa.nome';

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Matrícula','Nome','Perfil','Lotação','Cargo','Situação');
        $align = array('center','center','left','center','left','left','center');
        $titulo = 'Servidores com mais de uma matrícula ativa';
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
            if($this->lista){
                callout("Algum erro no sistema, favor verificar. Somente uma matrícula deveria estar ativa");
                $tabela->show();
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }

    ###########################################################
    
     /**
     * Método get_servidorComPerfilOutros
     * 
     * Servidor Ativo com perfil outros
     */
    
    public function get_servidorComPerfilOutros($idServidor = NULL)
    {
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
        $titulo = 'Servidor com perfil outros';
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
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }

    ###########################################################
    
     /**
     * Método get_servidorSemPerfil
     * 
     * Servidor com perfil outros
     */
    
    public function get_servidorSemPerfil($idServidor = NULL)
    {
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
        $titulo = 'Servidor sem perfil cadastrado';
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
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }

    ###########################################################
    
     /**
     * Método get_servidorTecnicoEstatutarioSemConcurso
     * 
     * Servidor Concursado sem concurso cadastrado
     */
    
    public function get_servidorTecnicoEstatutarioSemConcurso($idServidor = NULL)
    {
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
        $label = array('IdFuncional','Matrícula','Admissão','Nome','Perfil','Lotação','Cargo','Situação');
        $align = array('center','center','center','left','center','left','left','center');
        $titulo = 'Servidor técnico estatutário sem concurso cadastrado';
        $classe = array(NULL,NULL,NULL,NULL,NULL,"Pessoal","Pessoal");
        $rotina = array(NULL,NULL,NULL,NULL,NULL,"get_lotacao","get_cargo");
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
                callout("Todo servidor concursado deve ter cadastrado o concurso no qual foi aprovado.");
                $tabela->show();
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }

     ###########################################################
    
     /**
     * Método get_servidorProfessorEstatutarioSemConcurso
     * 
     * Servidor Concursado sem concurso cadastrado
     */
    
    public function get_servidorProfessorEstatutarioSemConcurso($idServidor = NULL)
    {
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
                      AND (idCargo = 128 OR idCargo = 129)';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY dtAdmissao,tbpessoa.nome';

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Matrícula','Admissão','Nome','Perfil','Lotação','Cargo','Situação');
        $align = array('center','center','center','left','center','left','left','center');
        $titulo = 'Servidor professor estatutário sem concurso cadastrado';
        $classe = array(NULL,NULL,NULL,NULL,NULL,"Pessoal","Pessoal");
        $rotina = array(NULL,NULL,NULL,NULL,NULL,"get_lotacao","get_cargo");
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
                callout("Todo servidor concursado deve ter cadastrado o concurso no qual foi aprovado.");
                $tabela->show();
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }

    ###########################################################
    
     /**
     * Método get_cargoComissaoNomeacaoIgualExoneracao
     * 
     * Cargo em comissão nomeado e exonerado no mesmo dia?!
     */
    
    public function get_cargoComissaoNomeacaoIgualExoneracao($idServidor = NULL)
    {
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);

        $select = 'SELECT distinct tbservidor.idFuncional,
                        tbservidor.matricula,
                        tbpessoa.nome,
                        tbcomissao.dtNom,
                        tbcomissao.dtExo,
                        concat(tbcomissao.descricao," ",if(protempore = 1," (pro tempore)","")),
                        concat(tbtipocomissao.simbolo," - ",tbtipocomissao.descricao),
                        tbcomissao.descricao,
                        tbservidor.idServidor
                   FROM tbservidor LEFT JOIN tbpessoa USING (idpessoa)
                                   LEFT JOIN tbcomissao USING (idServidor)
                                        JOIN tbtipocomissao USING (idTipoComissao)
                   WHERE tbtipocomissao.ativo AND (tbcomissao.dtNom = tbcomissao.dtExo)';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY 7, tbcomissao.descricao, 4 desc';
              

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Matrícula','Nome','Nomeação','Exoneração','Descrição');
        $align = array('center','center','left','center','center','left');
        $titulo = 'Cargo em comissão nomeado e exonerado no mesmo dia';
        #$classe = array(NULL,NULL,NULL,NULL,"Pessoal","Pessoal");
        #$rotina = array(NULL,NULL,NULL,NULL,"get_lotacao","get_cargo");
        $funcao = array(NULL,"dv",NULL,"date_to_php","date_to_php");
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
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }

    ###########################################################
    
     /**
     * Método get_servidorCom10MesesLicencaSemVencimento
     * 
     * Cargo em comissão nomeado e exonerado no mesmo dia?!
     */
    
    public function get_servidorCom10MesesLicencaSemVencimento($idServidor = NULL){
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);

        $select = 'SELECT tbservidor.idFuncional,
                          tbservidor.matricula,
                          tbpessoa.nome,
                          tblicenca.dtInicial,
                          tblicenca.numDias,
                          ADDDATE(dtInicial,numDias-1),
                          DATEDIFF(now(),dtInicial),
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idpessoa)
                                     LEFT JOIN tblicenca USING (idServidor)
                    WHERE situacao = 1 
                      AND idTpLicenca = 16              
                      AND ADDDATE(dtInicial,numDias-1) > NOW()
                      AND (DATEDIFF(now(),dtInicial) > 300 AND DATEDIFF(now(),dtInicial) < 365)';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY 3';
              

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Matrícula','Nome','Data Inicial','Dias','Término',"Dias Fruídos");
        $align = array('center','center','left','center','center','center');
        $titulo = 'Servidor se aproximando de 1 ano em licença sem vencimentos.';
        #$classe = array(NULL,NULL,NULL,NULL,"Pessoal","Pessoal");
        #$rotina = array(NULL,NULL,NULL,NULL,"get_lotacao","get_cargo");
        $funcao = array(NULL,"dv",NULL,"date_to_php",NULL,"date_to_php");
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
                return $titulo." Avisar que após 1 ano ele terá que pagar o Rio Previdência ou pedir exoneração.";
            }elseif($this->lista){
                callout("Avisar que após 1 ano ele terá que pagar o Rio Previdência ou pedir exoneração.");
                $tabela->show();
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }

    ###########################################################
    
     /**
     * Método get_servidorComMaisde1AnoLicencaSemVencimento
     * 
     * Cargo em comissão nomeado e exonerado no mesmo dia?!
     */
    
    public function get_servidorComMaisde1AnoLicencaSemVencimento($idServidor = NULL)
    {
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);

        $select = 'SELECT tbservidor.idFuncional,
                          tbservidor.matricula,
                          tbpessoa.nome,
                          tblicenca.dtInicial,
                          tblicenca.numDias,
                          ADDDATE(dtInicial,numDias-1),
                          DATEDIFF(now(),dtInicial),
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idpessoa)
                                     LEFT JOIN tblicenca USING (idServidor)
                    WHERE situacao = 1 
                      AND idTpLicenca = 16              
                      AND ADDDATE(dtInicial,numDias-1) > NOW()
                      AND DATEDIFF(now(),dtInicial) > 365';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY 3';
        
        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Matrícula','Nome','Data Inicial','Dias','Término',"Dias Fruídos");
        $align = array('center','center','left','center','center','center');
        $titulo = 'Servidor com mais de 1 ano em licença sem vencimentos.';
        #$classe = array(NULL,NULL,NULL,NULL,"Pessoal","Pessoal");
        #$rotina = array(NULL,NULL,NULL,NULL,"get_lotacao","get_cargo");
        $funcao = array(NULL,"dv",NULL,"date_to_php",NULL,"date_to_php");
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
       
       if($count > 0){
            if(!is_null($idServidor)){
                return $titulo." Certifique que ele esteja pagando em separado o Rio Previdência.";
            }elseif($this->lista){
                callout("Certifique que ele esteja pagando em separado o Rio Previdência ou peça exoneração.");
                $tabela->show();
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }

    ###########################################################
    
     /**
     * Método get_servidorSemIdFuncional
     * 
     * Exibe servidor ativo sem id Funcional cadastrado
     */
    
    public function get_servidorSemIdFuncional($idServidor = NULL) {
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
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }

    ###########################################################
    
     /**
     * Método get_servidorSemDtNasc
     * 
     * Servidor sem data de nasciment cadastrada
     */
    
    public function get_servidorSemDtNasc($idServidor = NULL) {
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
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }
    
    ##########################################################
    
     /**
     * Método get_servidorCedidoLotacaoErrada
     * 
     * Servidor cedido pela UENF que não está lotado na reitoria cedidos
     */
    
    public function get_servidorCedidoLotacaoErrada($idServidor = NULL) {
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
                callout("O servidor cedido pela deve estar cadastrado no setor Reitoria - Cessão.");
                $tabela->show();
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }
    
    ##########################################################
    
     /**
     * Método get_servidorEstatutarioSemCargo
     * 
     * Servidor estatutário sem cargo cadastrado:
     */
    
    public function get_servidorEstatutarioSemCargo($idServidor = NULL) {
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
        $label = array('IdFuncional','Matrícula','Nome','Perfil','Lotação');
        $align = array('center','center','left','center','left');
        $titulo = 'Servidor(es) estatutário(s) sem cargo cadastrado.';
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
                #callout("Servidor sem Id Funcional cadastrado no Sistema");
                $tabela->show();
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorSemCargo
     * 
     * Servidor NÃO estatutário sem cargo cadastrado:
     */
    
    public function get_servidorSemCargo($idServidor = NULL) {
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
                    WHERE (idCargo IS NULL OR idCargo = 0)
                      AND situacao = 1
                      AND idPerfil <> 1';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';                 

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Matrícula','Nome','Perfil','Lotação');
        $align = array('center','center','left','center','left');
        $titulo = 'Servidor(es) sem cargo cadastrado.';
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
                #callout("Servidor sem Id Funcional cadastrado no Sistema");
                $tabela->show();
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorDuplicado
     * 
     * Servidor Duplicado no Sistema
     */
    
    public function get_servidorDuplicado($idServidor = NULL) {
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
        $titulo = 'Servidor(es) Duplicado(s) no sistema.';
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
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorCedidoSemInfoCedente
     * 
     * Servidor cedido PARA a UENF sem informação do órgão cedente
     */
    
    public function get_servidorCedidoSemInfoCedente($idServidor = NULL) {
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
        $titulo = 'Servidor(es) cedido(s) para UENF sem informações da cessão cadastrada';
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
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }
    
    ###########################################################
    
     /**
     * Método get_servidorInativoComPerfilOutros
     * 
     * Servidor Inativo com perfil outros
     */
    
    public function get_servidorInativoComPerfilOutros($idServidor = NULL)
    {
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
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorInativoSemMotivoSaida
     * 
     * Servidor inativo sem motivo de saída:
     */
    
    public function get_servidorInativoSemMotivoSaida($idServidor = NULL) {
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
                    WHERE (idCargo IS NULL OR idCargo = 0)
                      AND situacao <> 1
                      AND (motivo is NULL OR motivo = 0)';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';                 

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Matrícula','Nome','Perfil','Cargo');
        $align = array('center','center','left','center','left');
        $titulo = 'Servidor(es) inativo(s) sem motivo de saída cadastrado.';
        $classe = array(NULL,NULL,NULL,NULL,"Pessoal");
        $rotina = array(NULL,NULL,NULL,NULL,"get_cargo");
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
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }

    ##########################################################
    
     /**
     * Método get_servidorInativoSemdataSaida
     * 
     * Servidor inativo sem data de saída:
     */
    
    public function get_servidorInativoSemdataSaida($idServidor = NULL) {
        $servidor = new Pessoal();
        $metodo = explode(":",__METHOD__);
        

        $select = 'SELECT idfuncional,
                          matricula,
                          tbpessoa.nome,
                          tbperfil.nome,                          
                          dtDemissao,
                          idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbperfil USING (idPerfil)
                    WHERE (idCargo IS NULL OR idCargo = 0)
                      AND situacao <> 1
                      AND (dtDemissao IS NULL OR dtDemissao = "")';
                if(!is_null($idServidor)){
                    $select .= ' AND idServidor = "'.$idServidor.'"';
                }                
        $select .= ' ORDER BY tbpessoa.nome';                 

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('IdFuncional','Matrícula','Nome','Perfil','Saída');
        $align = array('center','center','left','center','left');
        $titulo = 'Servidor(es) inativo(s) sem data de saída cadastrada.';
        $funcao = array(NULL,NULL,NULL,NULL,"date_to_php");
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
       
        if($count > 0){
            if(!is_null($idServidor)){
                return $titulo;
            }elseif($this->lista){
                #callout("Servidor sem Id Funcional cadastrado no Sistema");
                $tabela->show();
                set_session('alertas',$metodo[2]);
            }else{
                $link = new Link($count.' '.$titulo,"?fase=alertas&alerta=".$metodo[2]);
                $link->set_id("checkupResumo");
                echo "<li>";
                $link->show();
                echo "</li>";
            }
        }
    }

    ##########################################################
    
    
    
}
