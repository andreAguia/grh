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

	
    private $matricula = null;  //informa se será somente para um servidor ou se será para todos
    private $lista = true;      //informa se será listagem ou somente contagem dos registros
        
    ###########################################################

    /**
     * Método construct
     * 
     * Faz um checkup
     */
    
    public function __construct($lista = true,$matricula = null)
    {
        $this->lista = $lista;
        $this->matricula = $matricula;        
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
       
        $select = 'SELECT tbfuncionario.matricula,
                  tbpessoa.nome,
                  tbperfil.nome,
                  tbtipolicenca.nome,
                  tblicenca.dtInicial,
                  tblicenca.numDias,
                  ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)
             FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                LEFT JOIN tblicenca ON (tbfuncionario.matricula = tblicenca.matricula)
                                LEFT JOIN tbtipolicenca ON (tblicenca.idTpLicenca = tbtipolicenca.idTpLicenca)
                                LEFT JOIN tbperfil ON(tbfuncionario.idPerfil=tbperfil.idPerfil)
            WHERE tbfuncionario.Sit = 1
              AND YEAR(ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)) = "'.date('Y').'"';
        
        if(is_null($this->matricula))
            $select .= 'ORDER BY 7';
        else    ## parei aqui
            $select .= 'AND matricula = "'.$this->matricula.'" ORDER BY 7';

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $titulo = 'Servidores com Licença Terminando em '.date('Y');
        $label = array('Matrícula','Nome','Perfil','Licença','Data Inicial','Dias','Data Final');
        $width = array(10,30,10,20,10,5,10);
        $funcao = array('dv',null,null,null,"date_to_php",null,"date_to_php");
        $align = array('center','left');
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_cabecalho($label,$width,$align);
        $tabela->set_titulo($titulo);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('matricula');
        
        if ($count <> 0){
            if($this->lista){
                $tabela->show();
            }else{
                p($count.' '.$titulo,"checkupResumo");
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
            
            $select = '(SELECT DISTINCT tbfuncionario.matricula,
                  tbfuncionario.idFuncional,  
                  tbpessoa.nome,
                  tbfuncionario.dtadmissao,
                  CONCAT(MAX(tbtrienio.percentual),"%"),
                  MAX(tbtrienio.dtInicial),
                  DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR)
             FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                LEFT JOIN tbtrienio ON (tbtrienio.matricula = tbfuncionario.matricula)
            WHERE tbfuncionario.Sit = 1
              AND idPerfil = 1
         GROUP BY tbfuncionario.matricula
         HAVING YEAR (DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR)) = '.date('Y').'
         ORDER BY 7)
         UNION
         (SELECT DISTINCT tbfuncionario.matricula,
                  tbfuncionario.idFuncional,  
                  tbpessoa.nome,
                  tbfuncionario.dtadmissao,
                  "",
                  "",
                  DATE_ADD(tbfuncionario.dtadmissao, INTERVAL 3 YEAR)
             FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                LEFT JOIN tbtrienio ON (tbtrienio.matricula = tbfuncionario.matricula)
            WHERE tbfuncionario.Sit = 1
              AND idPerfil = 1              
         GROUP BY tbfuncionario.matricula
         HAVING YEAR (DATE_ADD(tbfuncionario.dtadmissao, INTERVAL 3 YEAR)) = '.date('Y').'
             AND MAX(tbtrienio.dtInicial) IS NULL
         ORDER BY 7)
         ORDER BY 7';		

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('Matrícula','IdFuncional','Nome','Admissão','Último Percentual','Último Triênio','Próximo Triênio');
        $width = array(10,10,35,10,10,10,10);
        $align = array('center','center','left');
        $titulo = 'Servidores com Triênio Vencendo em '.date('Y');
        $funcao = array(null,null,null,"date_to_php",null,"date_to_php","date_to_php");
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_cabecalho($label,$width,$align);
        $tabela->set_titulo($titulo);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('matricula');
        
        if ($count <> 0){
            if($this->lista){
                $tabela->show();
            }else{
                p($count.' '.$titulo,"checkupResumo");
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
        
        $select = '(SELECT DISTINCT tbfuncionario.matricula,
                  tbfuncionario.idFuncional,  
                  tbpessoa.nome,
                  tbfuncionario.dtadmissao,
                  CONCAT(MAX(tbtrienio.percentual),"%"),
                  MAX(tbtrienio.dtInicial),
                  DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR)
             FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                LEFT JOIN tbtrienio ON (tbtrienio.matricula = tbfuncionario.matricula)
            WHERE tbfuncionario.Sit = 1
              AND idPerfil = 1
         GROUP BY tbfuncionario.matricula
         HAVING YEAR (DATE_ADD(MAX(tbtrienio.dtInicial), INTERVAL 3 YEAR)) < '.date('Y').'
         ORDER BY 7)
         UNION
         (SELECT DISTINCT tbfuncionario.matricula,
                  tbfuncionario.idFuncional,  
                  tbpessoa.nome,
                  tbfuncionario.dtadmissao,
                  "",
                  "",
                  DATE_ADD(tbfuncionario.dtadmissao, INTERVAL 3 YEAR)
             FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                LEFT JOIN tbtrienio ON (tbtrienio.matricula = tbfuncionario.matricula)
            WHERE tbfuncionario.Sit = 1
              AND idPerfil = 1              
         GROUP BY tbfuncionario.matricula
         HAVING YEAR (DATE_ADD(tbfuncionario.dtadmissao, INTERVAL 3 YEAR)) < '.date('Y').'
             AND MAX(tbtrienio.dtInicial) IS NULL
         ORDER BY 7)
         ORDER BY 7';		

    $result = $servidor->select($select);
    $count = $servidor->count($select);

    # Cabeçalho da tabela
    $label = array('Matrícula','IdFuncional','Nome','Admissão','Último Percentual','Último Triênio','Deveriam ter recebido em:');
    $width = array(10,10,35,10,10,10,10);
    $align = array('center','center','left');
    $titulo = 'Servidores com Triênio Vencido antes de '.date('Y');
    $funcao = array(null,null,null,"date_to_php",null,"date_to_php","date_to_php");
    $linkEditar = 'servidor.php?fase=editar&id=';

    # Exibe a tabela
    $tabela = new Tabela();
    $tabela->set_conteudo($result);
    $tabela->set_cabecalho($label,$width,$align);
    $tabela->set_titulo($titulo);
    $tabela->set_funcao($funcao);
    $tabela->set_editar($linkEditar);
    $tabela->set_idCampo('matricula');
    
        if ($count <> 0){
            if($this->lista){
                $tabela->show();
            }else{
                p($count.' '.$titulo,"checkupResumo");
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
        
        $select = 'SELECT tbfuncionario.matricula,
                  tbpessoa.nome,
                  tbdependente.nome,
                  tbdependente.dtNasc,
                  dtTermino,
                  ciExclusao,
                  processo
             FROM tbdependente JOIN tbpessoa ON(tbpessoa.idpessoa = tbdependente.idpessoa)
                               JOIN tbfuncionario ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
            WHERE tbfuncionario.Sit = 1
              AND YEAR(dtTermino) = "'.date('Y').'"
         ORDER BY dtTermino';

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $titulo = 'Servidores com o Auxílio Creche vencendo em '.date('Y');
        $label = array("Matrícula","Servidor","Dependente","Nascimento","Término do Aux.","CI Exclusão","Processo");
        $width = array(10,20,20,10,10,10,15);
        $funcao = array("dv",null,null,"date_to_php","date_to_php");
        $align = array('center');
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_cabecalho($label,$width,$align);
        $tabela->set_titulo($titulo);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('matricula');
        
        if ($count <> 0){
            if($this->lista){
                $tabela->show();
            }else{
                p($count.' '.$titulo,"checkupResumo");
            }
        }
    }

    ###########################################################
    
     /**
     * Método get_motoristaCarteiraVencida
     * 
     * Motoristas com carteira de habilitação vencida no sistema
     */
    
    public function get_motoristaCarteiraVencida()
    {
        $servidor = new Pessoal();

        $select = 'SELECT tbfuncionario.matricula,
                        tbfuncionario.idFuncional,  
                        tbpessoa.nome,
                        tbdocumentacao.dtVencMotorista
                    FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                        LEFT JOIN tbdocumentacao ON (tbdocumentacao.idPessoa = tbpessoa.idPessoa)
                    WHERE tbfuncionario.Sit = 1
                    AND tbfuncionario.idcargo = 63
                    AND tbdocumentacao.dtVencMotorista < now()
                ORDER BY tbpessoa.nome';		

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('Matrícula','IdFuncional','Nome','Data da Carteira');
        $width = array(10,10,65,10);
        $align = array('center','center','left');
        $titulo = 'Motoristas com Carteira de Habilitação Vencida';
        $funcao = array(null,null,null,"date_to_php");
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_cabecalho($label,$width,$align);
        $tabela->set_titulo($titulo);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('matricula');
        
        if ($count <> 0){
            if($this->lista){
                $tabela->show();
            }else{
                p($count.' '.$titulo,"checkupResumo");
            }
        }
    }
    
    ###########################################################
    
     /**
     * Método get_motoristaSemDataCarteira
     * 
     * Motoristas com carteira de habilitação sem data de vencimento cadastrada no sistema
     */
    
    public function get_motoristaSemDataCarteira()
    {
        $servidor = new Pessoal();

        $select = 'SELECT tbfuncionario.matricula,
                        tbfuncionario.idFuncional,  
                        tbpessoa.nome,
                        tbdocumentacao.dtVencMotorista
                    FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                        LEFT JOIN tbdocumentacao ON (tbdocumentacao.idPessoa = tbpessoa.idPessoa)
                    WHERE tbfuncionario.Sit = 1
                    AND tbfuncionario.idcargo = 63
                    AND tbdocumentacao.dtVencMotorista is null
                ORDER BY tbpessoa.nome';		

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('Matrícula','IdFuncional','Nome','Data da Carteira');
        $width = array(10,10,65,10);
        $align = array('center','center','left');
        $titulo = 'Motoristas com carteira de habilitação sem data de vencimento cadastrada no sistema';
        $funcao = array(null,null,null,"date_to_php");
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_cabecalho($label,$width,$align);
        $tabela->set_titulo($titulo);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('matricula');
        
        if ($count <> 0){
            if($this->lista){
                $tabela->show();
            }else{
                p($count.' '.$titulo,"checkupResumo");
            }
        }
    }
    
    ###########################################################
    
     /**
     * Método get_motoristaSemCarteira
     * 
     * Motorista sem número da carteira de habilitação cadastrada:
     */
    
    public function get_motoristaSemCarteira()
    {
        $servidor = new Pessoal();

        $select = 'SELECT tbfuncionario.matricula,
                        tbfuncionario.idFuncional,  
                        tbpessoa.nome
                    FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                        LEFT JOIN tbdocumentacao ON (tbdocumentacao.idPessoa = tbpessoa.idPessoa)
                    WHERE tbfuncionario.Sit = 1
                    AND tbfuncionario.idcargo = 63
                    AND (tbdocumentacao.motorista is null OR tbdocumentacao.motorista ="")
                ORDER BY tbpessoa.nome';		

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('Matrícula','IdFuncional','Nome');
        $width = array(10,10,75);
        $align = array('center','center');
        $titulo = 'Motorista sem número da carteira de habilitação cadastrada:';
        $funcao = array(null);
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_cabecalho($label,$width,$align);
        $tabela->set_titulo($titulo);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('matricula');
        
        if ($count <> 0){
            if($this->lista){
                $tabela->show();
            }else{
                p($count.' '.$titulo,"checkupResumo");
            }
        }
    }
    
    ###########################################################
    
     /**
     * Método get_estatutarioSemCargo
     * 
     * Servidor estatutário sem cargo cadastrado:
     */
    
    public function get_estatutarioSemCargo()
    {
        $servidor = new Pessoal();

        $select = 'SELECT tbfuncionario.matricula,
                        tbfuncionario.idFuncional,  
                        tbpessoa.nome,
                        tbfuncionario.matricula
                    FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                    WHERE tbfuncionario.Sit = 1
                    AND tbfuncionario.idcargo = 0
                    AND tbfuncionario.idPerfil = 1
                ORDER BY tbpessoa.nome';		

        $result = $servidor->select($select);
        $count = $servidor->count($select);

        # Cabeçalho da tabela
        $label = array('Matrícula','IdFuncional','Nome','Lotação');
        $width = array(10,10,30,45);
        $align = array('center','center','left');
        $titulo = 'Servidores estatutários sem cargo cadastrado';
        $funcao = array(null,null,null,"get_lotacao");
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_cabecalho($label,$width,$align);
        $tabela->set_titulo($titulo);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('matricula');
       
        if ($count <> 0){
            if($this->lista){
                $tabela->show();
            }else{
                p($count.' '.$titulo,"checkupResumo");
            }
        }
    }
    
    ###########################################################

    /**
     * Método get_solicitaProgressao
     * 
     * Servidores com direito a solicitar progressão por antiguidade ou merecimento
     */
    
    public function get_solicitaProgressao()
    {
        $servidor = new Pessoal();
       
        $select = 'SELECT tbfuncionario.matricula,
	                  tbpessoa.nome,
	                  tbperfil.nome,
	                  MAX(tbprogressao.dtInicial),
	                  (SELECT tbclasse.faixa 
                             FROM tbprogressao JOIN tbclasse ON (tbprogressao.idclasse = tbclasse.idclasse)
                            WHERE tbprogressao.matricula = tbfuncionario.matricula
                            ORDER BY dtinicial DESC LIMIT 1),
                          (SELECT faixa
                             FROM tbclasse LEFT JOIN tbplano ON (tbclasse.idplano = tbplano.idplano)
                            WHERE tbplano.planoAtual = 1
                              AND tbclasse.nivel = tbcargo.tpcargo
                         ORDER BY valor DESC LIMIT 1),
	                  DATE_ADD(MAX(tbprogressao.dtInicial), INTERVAL 2 YEAR),
                          tbcargo.tpcargo
                     FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
					LEFT JOIN tbprogressao ON (tbfuncionario.matricula = tbprogressao.matricula)                                
					LEFT JOIN tbperfil ON(tbfuncionario.idPerfil=tbperfil.idPerfil)					
                                             JOIN tbcargo on (tbfuncionario.idcargo = tbcargo.idcargo)
                    WHERE tbfuncionario.Sit = 1
                    AND tbfuncionario.idPerfil = 1
                    AND (SELECT tbclasse.faixa 
                        FROM tbprogressao JOIN tbclasse ON (tbprogressao.idclasse = tbclasse.idclasse)
                    WHERE tbprogressao.matricula = tbfuncionario.matricula
                    ORDER BY dtinicial DESC LIMIT 1) <> (SELECT faixa
                        FROM tbclasse LEFT JOIN tbplano ON (tbclasse.idplano = tbplano.idplano)
                    WHERE tbplano.planoAtual = 1
                        AND tbclasse.nivel = tbcargo.tpcargo
                    ORDER BY valor DESC LIMIT 1)
                    GROUP BY tbfuncionario.matricula
                    HAVING YEAR (DATE_ADD(MAX(tbprogressao.dtInicial), INTERVAL 2 YEAR)) = '.date('Y').'
                    ORDER BY 7';

        $result = $servidor->select($select);
        $count = $servidor->count($select); 
        
         #parei aqui

        # Cabeçalho da tabela
        $titulo = 'Servidores com Direito a Progressão em '.date('Y');
        $label = array('Matrícula','Nome','Perfil','Última Progressão Cadastrada','Classe em que o Servidor se encontra','Última Classe do Nível do Cargo do Servidor','Com direito a partir de:','Nível do Cargo do Servidor');
        $width = array(10,25,10,10,10,10,10,10);
        $funcao = array('dv',null,null,"date_to_php",null,null,"date_to_php");
        $align = array('center','left');
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_cabecalho($label,$width,$align);
        $tabela->set_titulo($titulo);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('matricula');
        
        if ($count <> 0){
            if($this->lista){
                $tabela->show();
            }else{
                p($count.' '.$titulo,"checkupResumo");
            }
        }
    }

    ###########################################################

    /**
     * Método get_solicitaProgressaoAtrasada
     * 
     * Servidores com direito a solicitar progressão por antiguidade ou merecimento antes do ano atual
     */
    
    public function get_solicitaProgressaoAtrasada()
    {
        $servidor = new Pessoal();
       
        $select = 'SELECT tbfuncionario.matricula,
	                  tbpessoa.nome,
	                  tbperfil.nome,
	                  MAX(tbprogressao.dtInicial),
	                  (SELECT tbclasse.faixa 
                             FROM tbprogressao JOIN tbclasse ON (tbprogressao.idclasse = tbclasse.idclasse)
                            WHERE tbprogressao.matricula = tbfuncionario.matricula
                            ORDER BY dtinicial DESC LIMIT 1),
                          (SELECT faixa
                             FROM tbclasse LEFT JOIN tbplano ON (tbclasse.idplano = tbplano.idplano)
                            WHERE tbplano.planoAtual = 1
                              AND tbclasse.nivel = tbcargo.tpcargo
                         ORDER BY valor DESC LIMIT 1),
	                  DATE_ADD(MAX(tbprogressao.dtInicial), INTERVAL 2 YEAR),
                          tbcargo.tpcargo
                     FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
					LEFT JOIN tbprogressao ON (tbfuncionario.matricula = tbprogressao.matricula)                                
					LEFT JOIN tbperfil ON(tbfuncionario.idPerfil=tbperfil.idPerfil)					
                                             JOIN tbcargo on (tbfuncionario.idcargo = tbcargo.idcargo)                    
                    WHERE tbfuncionario.Sit = 1
                    AND tbfuncionario.idPerfil = 1
                    AND (SELECT tbclasse.faixa 
                        FROM tbprogressao JOIN tbclasse ON (tbprogressao.idclasse = tbclasse.idclasse)
                    WHERE tbprogressao.matricula = tbfuncionario.matricula
                    ORDER BY dtinicial DESC LIMIT 1) <> (SELECT faixa
                        FROM tbclasse LEFT JOIN tbplano ON (tbclasse.idplano = tbplano.idplano)
                    WHERE tbplano.planoAtual = 1
                        AND tbclasse.nivel = tbcargo.tpcargo
                    ORDER BY valor DESC LIMIT 1)
                    GROUP BY tbfuncionario.matricula
                    HAVING YEAR (DATE_ADD(MAX(tbprogressao.dtInicial), INTERVAL 2 YEAR)) < '.date('Y').'
                    ORDER BY 7';

        $result = $servidor->select($select);
        $count = $servidor->count($select); 
        
         #parei aqui

        # Cabeçalho da tabela
        $titulo = 'Servidores com Direito a Progressão antes de '.date('Y');
        $label = array('Matrícula','Nome','Perfil','Última Progressão Cadastrada','Classe em que o Servidor se encontra','Última Classe do Nível do Cargo do Servidor','Com direito a partir de:','Nível do Cargo do Servidor');
        $width = array(10,25,10,10,10,10,10,10);
        $funcao = array('dv',null,null,"date_to_php",null,null,"date_to_php");
        $align = array('center','left');
        $linkEditar = 'servidor.php?fase=editar&id=';

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_cabecalho($label,$width,$align);
        $tabela->set_titulo($titulo);
        $tabela->set_funcao($funcao);
        $tabela->set_editar($linkEditar);
        $tabela->set_idCampo('matricula');
        
        if ($count <> 0){
            if($this->lista){
                $tabela->show();
            }else{
                p($count.' '.$titulo,"checkupResumo");
            }
        }
    }

    ###########################################################
}