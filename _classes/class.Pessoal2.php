<?php

class Pessoal2 extends Bd
{
    /** 
     * Classe de acesso ao Banco de Dados Pessoal
     * 
     * @author André Águia (Alat) - alataguia@gmail.com
     * 
     * @var private $servidor string localhost O nome do servidor do banco de dados
     * @var private $usuario  string NULL      O nome do usuário no banco de dados
     * @var private $senha    string NULL      A senha de acesso ao banco de dados
     * @var private $banco    string pessoal   O nome do banco de dados a ser acessado pela classe
     * @var private $sgdb     string mysql     O nome do SGDB a ser utilizado
     * @var private $tabela   string NULL      A tabela que está sendo acessada
     * @var private $idCampo  string NULL      O nome do campo id da tabela    
     */
	
    private $servidor = "localhost";
    private $usuario = "intranet";
    private $senha = "txzVHnMdh53ZWX9p";
    private $banco = "pessoal";
    #private $banco = "grh";
    private $sgdb = "mysql";
    private $tabela;
    private $idCampo;
    
###########################################################
	
    /**
     * Faz uma conexão
     */ 
    
    public function __construct(){
        parent::__construct($this->servidor,$this->usuario,$this->senha,$this->banco,$this->sgdb);		
    }
    
###########################################################
	
	/**
	 * M�todo set_tabela
	 * 
	 * @param  	$nomeTabela	-> Nome da tabela do banco de dados intra que ser� utilizada
	 */
	public function set_tabela($nomeTabela)
	{
            $this->tabela = $nomeTabela;
	}
	
	###########################################################
	
	/**
	 * M�todo set_idCampo
	 * 
	 * @param  	$idCampo)	-> Nome do campo chave da tabela
	 */
	public function set_idCampo($idCampo)
	{
            $this->idCampo = $idCampo;
	}
	
	###########################################################
	
	/**
	 * M�todo Gravar
	 */
	public function gravar($campos = NULL,$valor = NULL,$idValor = NULL,$tabela = NULL,$idCampo = NULL,$alerta = TRUE){
            
            if(is_null($tabela))
                $tabela = $this->tabela;

            if(is_null($idCampo))
                $idCampo = $this->idCampo;

            parent::gravar($campos,$valor,$idValor,$tabela,$idCampo,$alerta);
	}
	
	###########################################################
	
	/**
	 * M�todo Excluir
	 */
	public function excluir($idValor = NULL,$tabela = NULL,$idCampo = 'id'){
            
            # efetua a exclus�o
            parent::excluir($idValor,$this->tabela,$this->idCampo);

            return TRUE;		
	}
	
	###########################################################
	
	/**
	 * M�todo get_gratificacao
	 * informa gratifica��o de uma matr�cula(se houver)
	 * 
	 * @param	string $matricula matricula do servidor
	 */

	public function get_gratificacao($matricula)
	{
		$select = 'SELECT valor
		             FROM tbgratif
		            WHERE matricula = '.$matricula.'
                              AND current_date() >= dtInicial 
                              AND (dtFinal is NULL OR current_date() <= dtFinal)';
		
		$row = parent::select($select,FALSE);
		
		return $row[0];
					
	}
	
	###########################################################
	
	/**
	 * M�todo get_gratificacaoDtFinal
	 * informa a data de t�rmino da gratifica��o de uma matr�cula(se houver)
	 * 
	 * @param	string $matricula matricula do servidor
	 */

	public function get_gratificacaoDtFinal($matricula)
	{
            $select = 'SELECT dtFinal
                         FROM tbgratif
                        WHERE matricula = '.$matricula.'
                        ORDER BY dtInicial desc';
            $numero = parent::count($select);
            $row = parent::select($select,FALSE);
            

            # For�a como NULL caso seja em branco
            if((is_null($row[0])) OR ($row == ''))
                $row[0] = NULL;
            
            
            # Verifica se j� tem alguma gratifica��o ou se nunca teve
            if($numero == 0)
                return FALSE; # nunca teve gratifica��o
            else
                return $row[0]; # Informa se tem gratifica��o em aberto
	}
	
	###########################################################
	
	/**
	 * M�todo get_periodoDisponivel
	 * informa o per�odo dispon�vel de f�rias de um servidor
	 * 
	 * @param	string $matricula matricula do servidor
	 */

	public function get_periodoDisponivel($matricula)
	{
		$select = "SELECT anoExercicio,
	                  sum(numDias) as dias,
	                  status
	             FROM tbferias
	            WHERE matricula = '$matricula' AND
                      status <> 'cancelada'
             GROUP BY 1
             ORDER BY 1 DESC
             LIMIT 1";
		
		$row = parent::select($select,FALSE);
		
		# Informa o status
		if (is_null($row[0]))
                    $primeira = 1;
		else 
                    $primeira = 0;
		
		# Dias que sobram para serem 'gozados'
		if ($row[1] < 30)
	    {
	     	$dias = (30 - $row[1]);
	     	$ano = $row[0];
	    }
		else
		{
		 	$dias = 30;
		 	$ano = ($row[0] + 1);
		}
		
		return array($ano,$dias,$primeira);
					
	}
		
	###########################################################
		
	/**
	 * M�todo get_ramais
	 * Retorna um array com os setores e os ramais
	 */
	public function get_ramais()
	{
		$select =' SELECT concat(UADM," - ",DIR," - ",GER) as lotacao,
	                      ramais
	                 FROM tblotacao
	                WHERE ativo = "Sim"
                          AND GER <> "CEDIDO"
                          AND ramais <> ""
	             ORDER BY lotacao asc';
		
		# conecta com o banco
		$result = parent::select($select);
		return $result;
	
	}
	
	###########################################################
	
	/**
	 * M�todo get_salarioBase
	 * informa o sal�rio base de uma matr�cula
	 * 
	 * @param	string $matricula matricula do servidor
	 */

	public function get_salarioBase($matricula)
	{
		$select = 'SELECT tbclasse.valor
                             FROM tbprogressao, tbclasse
                             WHERE matricula = '.$matricula.'
                               AND tbprogressao.idClasse = tbclasse.idClasse
                      ORDER BY valor desc';
		
		$row = parent::select($select,FALSE);
		
		return $row[0];
					
	}
	
        ###########################################################
    
        /**
         * M�todo get_salarioTotal
         * informa o sal�rio Total de uma matr�cula
         * 
         * @param	string $matricula matricula do servidor
         */

        public function get_salarioTotal($matricula)
        {

            # Resumo financeira
            $salario = $this->get_salarioBase($matricula);
            $trienio = $this->get_trienioValor($matricula);
            $comissao = $this->get_salarioCargoComissao($matricula);
            $gratificacao = $this->get_gratificacao($matricula);
            $cessao = $this->get_salarioCessao($matricula);
            $total = $salario+$trienio+$comissao+$gratificacao+$cessao;

            return $total;
        }

        ###########################################################
	
	/**
	 * M�todo get_salarioCessao
	 * informa o sal�rio recebido pelo �rg�o de origem de um cedido
	 * 
	 * @param	string $matricula matricula do servidor
	 */

	public function get_salarioCessao($matricula)
	{
		$select = 'SELECT salario
                             FROM tbcedido
                            WHERE matricula = '.$matricula;
		
		$row = parent::select($select,FALSE);
		
		return $row[0];
					
	}
	
	###########################################################
        
	/**
	 * M�todo get_aniversariantes
	 * Exibe os niversariantes de um determinado m�s
	 * 
	 * @param	$mes	string	valor de 1 a 12 que informa o m�s
	 */
	public function get_aniversariantes($mes = NULL)
	{
		
		if (is_null($mes))
			$mes = date("n");
	
		$select = 'SELECT concat(date_format(tbpessoa.dtNasc,"%d/%m")," - ",tbpessoa.nome) as nasc,
					  date_format(tbpessoa.dtNasc,"%d")	
					   FROM tbpessoa, tbfuncionario
					   WHERE month(dtNasc) = '.$mes.' and
					         tbfuncionario.idPessoa = tbpessoa.idPessoa and
					         tbfuncionario.Sit = 1
					   ORDER BY nasc';
	
		# conecta com o banco
		
		# Pega o resultado do select
		$result = parent::select($select);
		
		return $result; 
	}
	
	###########################################################
	
	/**
	 * M�todo get_senha
	 * Informa a senha (criptografada) 
	 * 
	 * @param	string $matricula	matricula do servidor
	 */
	public function get_senha($matricula)
        { 

            $select = "SELECT senha_intra		  
                         FROM tbfuncionario
                        WHERE matricula = ".$matricula;

            # verifica se a matricula foi informada
            if(is_null($matricula))
                return 0;
            else
            {
                $result = parent::select($select,FALSE);
                return $result[0]; 
            }
        }
	
	###########################################################
	
	/**
	 * M�todo set_senha
	 * muda a senha de um usu�rio
	 * 
	 * @param	string 	$matricula 	-> matricula do servidor
	 * @param 	string	$senha		-> senha (n�o criptofrafada) a ser gravada (se nulo grava-se a senha padr�o)
	 */
	public function set_senha($matr,$senha = SENHA_PADRAO,$alert = TRUE)
	{
		# Grava a data quando � para senha padr�o (para controle dos 2 dias)
		if ($senha == SENHA_PADRAO)
			parent::gravar('ult_acesso',date("Y-m-d H:i:s"),$matr,'tbfuncionario','matricula',FALSE); 
			
		$senha = md5($senha);
		parent::gravar('senha_intra',$senha,$matr,'tbfuncionario','matricula',$alert);
	}
	
	###########################################################
	
	/**
	 * M�todo set_senhaNull
	 * muda a senha de um usu�rio para NULL (bloqueia o mesmo)
	 * 
	 * @param	string 	$matricula 	-> matricula do servidor
	 * @param 	string	$senha		-> senha (n�o criptofrafada) a ser gravada (se nulo grava-se a senha padr�o)
	 */
	public function set_senhaNull($matr,$alert = TRUE)
	{
		$senha = NULL;
		parent::gravar('senha_intra',$senha,$matr,'tbfuncionario','matricula',$alert);
		
	}
	###########################################################
		
	/**
	 * M�todo get_diasAusentes
	 * Informa, em dias, o per�odo entre a data atual
	 * e o �ltimo acesso do usu�rio 
	 *
	 * @param	string $matricula	matricula do servidor
	 */
	public function get_diasAusentes($matricula)
	{ 
		
		$select = "SELECT date_format(ult_acesso,'%d/%m/%Y')		  
					 FROM tbfuncionario
		        WHERE matricula = '$matricula'";
		
		# Pega o resultado do select
		$result = parent::select($select,FALSE);
		
		$data_Inicial = $result[0];
		  	
		$diferenca = dataDif($data_Inicial);	# chama o m�todo est�tico dataDiff
				
		return $diferenca;
		
		
	}
	
	######################################################################################
	
	/**
	 * M�todo get_lotacao
	 * Informa a lota��o atual do servidor
	 * 
	 * @param	string $matricula  matricula do servidor
	 */

	public function get_lotacao($matricula)
	
	{
		$select = 'SELECT  tblotacao.UADM,
                                   tblotacao.DIR,
                                   tblotacao.GER
                              FROM tbhistlot LEFT JOIN tblotacao on tbhistlot.lotacao = tblotacao.idlotacao
                             WHERE tbhistlot.matricula = '.$matricula.'
                             ORDER BY data DESC';
				
		$row = parent::select($select,FALSE);
		
		return $row[0].'-'.$row[1].'-'.$row[2];
		
	}
	
	###########################################################
	
	/**
	 * M�todo get_lotacaoNumServidores
	 * Informa o n�mero de servidores ativos nessa lota��o
	 * 
	 * @param	string $matricula  matricula do servidor
	 */

	public function get_lotacaoNumServidores($id)
	
	{
		$select = 'SELECT tbfuncionario.matricula
                             FROM tbfuncionario LEFT JOIN tbhistlot ON (tbfuncionario.matricula = tbhistlot.matricula)
                                    JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                              WHERE tbfuncionario.Sit = 1
                                AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.matricula = tbfuncionario.matricula)
                                AND tbhistlot.lotacao = '.$id;
				
		$numero = parent::count($select);
                return $numero;
		
	}
	
	###########################################################

	/**
	 * M�todo get_idlotacao
	 * Informa o id da lota��o atual do servidor
	 *
	 * @param	string $matricula  matricula do servidor
	 */

	public function get_idlotacao($matricula)

	{
		$select = 'SELECT  tblotacao.idlotacao
                              FROM tbhistlot LEFT JOIN tblotacao on tbhistlot.lotacao = tblotacao.idlotacao
                             WHERE tbhistlot.matricula = '.$matricula.'
                             ORDER BY data DESC';

		$row = parent::select($select,FALSE);
		return $row[0];

	}

	###########################################################

	
	/**
	 * Método get_cargo
	 * Informa o cargo do servidor
	 * 
	 * @param	string $matricula  matricula do servidor
	 */

	public function get_cargo($matricula)
	
	{
            # Pega o cargo do servidor
            $select = 'SELECT tbcargo.nome
                         FROM tbfuncionario LEFT JOIN tbcargo ON (tbfuncionario.idCargo=tbcargo.idCargo)
                        WHERE matricula = '.$matricula;

            $row = parent::select($select,FALSE);
            $cargo = $row[0];

            $comissao = $this->get_cargoComissao($matricula);

            if (is_null($comissao))
                return $cargo;
            else
                return $cargo.' ('.$comissao.')';
			
	}
		
	###########################################################

	
	/**
	 * M�todo get_perfil
	 * Informa o perfil do servidor
	 * 
	 * @param   string $matricula  matricula do servidor
	 */

	public function get_perfil($matricula)
	
	{
		# Pega o cargo do servidor
		$select = 'SELECT tbperfil.nome
                             FROM tbfuncionario LEFT JOIN tbperfil ON (tbfuncionario.idPerfil=tbperfil.idPerfil)
                            WHERE matricula = '.$matricula;
					 
		$row = parent::select($select,FALSE);
		$perfil = $row[0];
                
                return $perfil;
			
	}
		
	###########################################################

	
	/**
	 * M�todo get_idCargo
	 * Informa o id do cargo do servidor
	 * 
	 * @param	string $matricula  matricula do servidor
	 */

	public function get_idCargo($matricula)
	
	{
		# Pega o cargo do servidor
		$select = 'SELECT tbcargo.idCargo
                             FROM tbfuncionario LEFT JOIN tbcargo ON (tbfuncionario.idCargo=tbcargo.idCargo)
                            WHERE matricula = '.$matricula;
					 
		$row = parent::select($select,FALSE);
		$idcargo = $row[0];
                
		return $idcargo;
	}
		
	###########################################################
	
	/**
	 * M�todo get_idpessoa
	 * fornece o id_pessoa de uma matricula
	 * 
	 * @param	string $matricula matricula do servidor
	 */

	public function get_idPessoa($matricula)
	{
		$select = 'SELECT idPessoa
                             FROM tbfuncionario
                            WHERE matricula = '.$matricula;
		
		$id_pessoa = parent::select($select,FALSE);
				
		return $id_pessoa[0];
	}
	
	###########################################################
	
	/**
	 * M�todo get_idpessoaCPF
	 * fornece o id_pessoa de um CPF
	 * 
	 * @param	string $cpf cpf do servidor
	 */

	public function get_idpessoaCPF($cpf)
	{
		$select = 'SELECT idPessoa
                             FROM tbdocumentacao
                            WHERE cpf = "'.$cpf.'"';
		
		$idPessoa = parent::select($select,FALSE);
				
		return $idPessoa[0];                
	}
	
	###########################################################
	
	/**
	 * M�todo get_idpessoaPis
	 * fornece o id_pessoa de um PisF
	 * 
	 * @param	string $pis do servidor
	 */

	public function get_idpessoaPis($pis)
	{
		$select = 'SELECT idPessoa
                             FROM tbdocumentacao
                            WHERE pisPasep = "'.$pis.'"';
		
		$idPessoa = parent::select($select,FALSE);
				
		return $idPessoa[0];
                
	}
	
	##########################################################
	
	/**
	 * M�todo get_anoAdmissao
	 * informa o ano de admiss�o de um servidor
	 * 
	 * @param	string $matricula matricula do servidor
	 */

	function get_anoAdmissao($matricula)
	{
		$select = 'SELECT YEAR(dtAdmissao)
				     FROM tbfuncionario
				    WHERE matricula = '.$matricula;
		
		$ano = parent::select($select,FALSE);
				
		return $ano[0];
	}
	
	###########################################################
	
	/**
	 * M�todo get_dtAdmissao
	 * informa o ano de admiss�o de um servidor
	 * 
	 * @param	string $matricula matricula do servidor
	 */

	function get_dtAdmissao($matricula)
	{
		$select = 'SELECT dtAdmissao
                             FROM tbfuncionario
                            WHERE matricula = '.$matricula;
		
		$dt = parent::select($select,FALSE);
		
		return date_to_php($dt[0]);
	}
	
	###########################################################
	
	function get_idPerfil($matricula)
	
	/**
	 * M�todo get_idPerfil
	 * informa o id do perfil do servidor
	 * 
	 * @param	string $matricula matricula do servidor
	 */


	{
		$select = 'SELECT idPerfil
                             FROM tbfuncionario
                            WHERE matricula = '.$matricula;
		
		$row = parent::select($select,FALSE);
		
		return $row[0];
	}
	
	###########################################################

	function get_digito($matricula)
	
	/**
	 * M�todo get_digito
	 * informa o d�gito verificador de uma matr�cula
	 * 
	 * @param	string $matricula matricula do servidor
	 */
	
	{
		
		$ndig = 0;
		
		
		switch (strlen($matricula))
		{
		    case 4:
		        $matricula = "0".$matricula;
		        break;
		    case 3:
		        $matricula = "00".$matricula;
		        break;
		    case 2:
		        $matricula = "000".$matricula;
		        break;
		}
		
		
		
		$npos = substr($matricula,4,1);
		$npos = $npos * 2;
		if ($npos < 10) 
		   $ndig = $ndig + $npos;
		else
		   $ndig = $ndig + 1 + ($npos - 10);
		
		
		
		$npos = substr($matricula,3,1);
		$ndig = $ndig + $npos;
		
		
		
		$npos = substr($matricula,2,1);
		$npos = $npos * 2;
		if ($npos < 10)
		   $ndig = $ndig + $npos;
		else
		   $ndig = $ndig + 1 + ($npos - 10);
		
		
		
		$npos = substr($matricula,1,1);
		$ndig = $ndig + $npos;
		
		
		
		$npos = substr($matricula,0,1);
		$npos = $npos * 2;
		if ($npos < 10)
		   $ndig = $ndig + $npos;
		else
		   $ndig = $ndig + 1 + ($npos - 10);
		   
		
		$divisao = $ndig/10;
		$int_div = intval($divisao);
		$fra_div = $divisao - $int_div;
		$mod = $fra_div * 10;
			
		if ($mod == 0)
		    $ndig = 0;
		else
		    $ndig = 10 - $mod;
		
		return $ndig;
	}
	
	##########################################################################################

	function emFerias($matricula)
	
	# Fun��o que informa se a matricula est� em f�rias na data atual
	#
	# Par�metro: a matr�cula a ser pesquisada
	
	{
            # Monta o select
            $select = "SELECT idFerias 
                         FROM tbferias
                        WHERE matricula = '$matricula'
                          AND current_date() >= dtInicial 
                          AND current_date() <= ADDDATE(dtInicial,numDias-1)
                          AND status <> 'cancelada'";

            $row = parent::select($select,FALSE);

            if (is_null($row[0]))
                return 0;
            else 
                return 1;
	}
	
	##########################################################################################

	function emLicenca($matricula)
	
	
	# Fun��o que informa se a matricula est� em licanca na data atual
	#
	# Par�metro: a matr�cula a ser pesquisada
	
	{
            # Monta o select
            $select = "SELECT idLicenca 
                         FROM tblicenca
                        WHERE matricula = '$matricula'
                          AND current_date() >= dtInicial 
                          AND current_date() <= ADDDATE(dtInicial,numDias-1)";

            $row = parent::select($select,FALSE);

            if (is_null($row[0]))
                return 0;
            else 
                return 1;
	}
	
	##########################################################################################

	function get_licenca($matricula)
	
	
	# Fun��o que informa licenca de uma matr�cula
	#
	# Par�metro: a matr�cula a ser pesquisada
	
	{
		# Monta o select		
		$select = "SELECT tbtipolicenca.nome 
		             FROM tblicenca JOIN tbtipolicenca ON (tblicenca.idTpLicenca = tbtipolicenca.idTpLicenca)
                            WHERE matricula = '$matricula'
                              AND current_date() >= dtInicial 
                              AND current_date() <= ADDDATE(dtInicial,numDias-1)";
				
		$row = parent::select($select,FALSE);
		
		return $row[0];		
	
	}
	
	##########################################################################################

	function get_licencaPeriodo($idLicenca)
	
	
	# Fun��o que informa se a licen�a tem per�odo aquisitivo
	#
	# Par�metro: id do tipo de licen�a
	
	{
		# valida par�metro
                if(is_null($idLicenca))
                    return FALSE;
                
                # Monta o select
                $select = 'SELECT dtPeriodo
                             FROM tbtipolicenca
                            WHERE idTpLicenca = '.$idLicenca;
				
		$row = parent::select($select,FALSE);
		
		return $row[0];		
	
	}
	
	##########################################################################################

	function get_licencaProcesso($idLicenca)
	
	
	# Fun��o que informa se a licen�a necessita um processo administrativo
	#
	# Par�metro: id do tipo de licen�a
	
	{
		# valida par�metro
                if(is_null($idLicenca))
                    return FALSE;
                
                # Monta o select
                $select = 'SELECT processo
                             FROM tbtipolicenca
                            WHERE idTpLicenca = '.$idLicenca;
				
		$row = parent::select($select,FALSE);
		
		return $row[0];		
	
	}
	
	##########################################################################################

	function get_licencaPublicacao($idLicenca)
	
	
	# Fun��o que informa se a licen�a necessita de publica��o no DOERJ
	#
	# Par�metro: id do tipo de licen�a
	
	{
		# valida par�metro
                if(is_null($idLicenca))
                    return FALSE;
                
                # Monta o select
                $select = 'SELECT publicacao
                             FROM tbtipolicenca
                            WHERE idTpLicenca = '.$idLicenca;
				
		$row = parent::select($select,FALSE);
		
		return $row[0];		
	
	}
	
	##########################################################################################

	function get_licencaPericia($idLicenca)
	
	
	# Fun��o que informa se esse tipo de licen�a necessita de per�cia (licen�a m�dica)
	#
	# Par�metro: id do tipo de licen�a
	
	{
		# valida par�metro
                if(is_null($idLicenca))
                    return FALSE;
                
                # Monta o select		
                $select = 'SELECT pericia
                             FROM tbtipolicenca
                            WHERE idTpLicenca = '.$idLicenca;
				
		$row = parent::select($select,FALSE);
		
		return $row[0];		
	
	}
	
	##########################################################################################

	function get_licencaNome($idLicenca)
	
	
	# Fun��o que informa o nome do tipo de licen�a
	#
	# Par�metro: id do tipo de licen�a
	
	{
		# valida par�metro
                if(is_null($idLicenca))
                    return FALSE;
                
                # Monta o select		
                $select = 'SELECT nome
                             FROM tbtipolicenca
                            WHERE idTpLicenca = '.$idLicenca;
				
		$row = parent::select($select,FALSE);
		
		return $row[0];		
	
	}
	
	##########################################################################################

	function get_licencaSexo($idLicenca)
	
	
	# Fun��o que informa limita��o por genero (sexo) do tipo de licen�a
	#
	# Par�metro: id do tipo de licen�a
	
	{
		# valida par�metro
                if(is_null($idLicenca))
                    return FALSE;
                
                # Monta o select		
                $select = 'SELECT limite_sexo
                             FROM tbtipolicenca
                            WHERE idTpLicenca = '.$idLicenca;
				
		$row = parent::select($select,FALSE);
		
		return $row[0];		
	
	}
	
	##########################################################################################

	function get_licencaDias($idLicenca)
	
	
	# Fun��o que informa a quantidade de dias fixos para esse tipo de licen�a
	#
	# Par�metro: id do tipo de licen�a
	
	{
		# valida par�metro
                if(is_null($idLicenca))
                    return FALSE;
                
                # Monta o select		
                $select = 'SELECT periodo
                             FROM tbtipolicenca
                            WHERE idTpLicenca = '.$idLicenca;
				
		$row = parent::select($select,FALSE);
		
		return $row[0];		
	
	}
	
	##########################################################################################

	function get_tipoLicenca($idLicenca)
	
	
	# Fun��o que informa o tipo da licen�a de uma licen�a de um servidor
	#
	# Par�metro: id da licen�a
	
	{
		# valida par�metro
                if(is_null($idLicenca))
                    return FALSE;
                
                # Monta o select		
                $select = 'SELECT idTpLicenca
                             FROM tblicenca
                            WHERE idLicenca = '.$idLicenca;
				
		$row = parent::select($select,FALSE);
		
		return $row[0];		
	
	}
	
	##########################################################################################

	function get_nomeTipoLicenca($idTpLicenca)
	
	
	# Fun��o que informa o nome de um tipo da licen�a
	#
	# Par�metro: id do tipo da licen�a
	
	{
		# valida par�metro
                if(is_null($idTpLicenca))
                    return FALSE;
                
                # Monta o select		
                $select = 'SELECT nome
                             FROM tbtipolicenca
                            WHERE idtpLicenca = '.$idTpLicenca;
				
		$row = parent::select($select,FALSE);
		
		return $row[0];		
	
	}
	
	###########################################################
	
	/**
	 * M�todo get_situacao
	 * informa a situa��o (ativo ou inativo) de um servidor
	 * 
	 * @param	string $matricula matricula do servidor
	 */

	function get_situacao($matricula)
	{
		$select = 'SELECT tbsituacao.sit
                             FROM tbfuncionario LEFT JOIN tbsituacao ON (tbfuncionario.Sit = tbsituacao.idSit)
                            WHERE matricula = '.$matricula;
		
		$situacao = parent::select($select,FALSE);
				
		return $situacao[0];
	}
	
	###########################################################
	
	/**
	 * M�todo get_idPessoaAtiva
	 * informa se a pessoa tem alguma matr�cula ativa
	 * 
	 * @param	integer $idPessoa   idPessoa do servidor
	 */

	function get_idPessoaAtiva($idPessoa)
	{
		$select = 'SELECT matricula
                             FROM tbfuncionario
                            WHERE Sit = 1 AND idPessoa = '.$idPessoa;
		
		$situacao = parent::select($select,FALSE);
				
		return $situacao[0];
	}
	
	###########################################################
	
	/**
	 * M�todo get_nome
	 * fornece o nome de uma matricula
	 * 
	 * @param	string $matricula matricula do servidor
	 */

	function get_nome($matricula)
	{
		$select = 'SELECT tbpessoa.nome
			     FROM tbfuncionario JOIN tbpessoa ON(tbfuncionario.idPessoa = tbpessoa.idPessoa)
			    WHERE matricula = '.$matricula;
		
		if($matricula == 0)
			$nome[0] = "";
		else 
			$nome = parent::select($select,FALSE);

	
		return $nome[0];
	}
	
	###########################################################
	
	/**
	 * M�todo get_sexo
	 * informa o sexo de uma matricula
	 * 
	 * @param	string $matricula matricula do servidor
	 */

	function get_sexo($matricula)
	{
		$select = 'SELECT tbpessoa.sexo
			     FROM tbfuncionario JOIN tbpessoa ON(tbfuncionario.idPessoa = tbpessoa.idPessoa)
			    WHERE matricula = '.$matricula;
		
		if($matricula == 0)
			$nome[0] = "";
		else 
			$nome = parent::select($select,FALSE);

	
		return $nome[0];
	}
	
	###########################################################
	
	/**
	 * M�todo get_nomeidPessoa
	 * fornece o nome de um idPessoa
	 * 
     * @param   integer $idPessoa    idPessoa do servidor
	 */

	function get_nomeidPessoa($idPessoa)
	{
		$select = 'SELECT tbpessoa.nome
			     FROM tbpessoa
                            WHERE idPessoa = '.$idPessoa;
		
		$nome = parent::select($select,FALSE);
	
		return $nome[0];
	}
	
	###########################################################
	
	/**
	 * M�todo get_cargoComissao
	 * Informa o cargo em Comiss�o do Servidor (se tiver)
	 * 
	 * @param	string $matricula  matricula do servidor
	 */

	function get_cargoComissao($matricula)
	
	{
		# Pega o id do cargo em comissão (se houver)		 
		$select = 'SELECT idComissao
			     FROM tbcomissao
			    WHERE ((CURRENT_DATE BETWEEN dtNom AND dtExo)
                               OR (dtExo is NULL))
                            AND matricula = '.$matricula;
					
		$row = parent::select($select,FALSE);
		$idCargo = $row[0];
		
		# Pega o nome do id do cargo em comissão
		if (!is_null($idCargo))
		{
			$select ='SELECT tbtipocomissao.descricao 
				    FROM tbcomissao 
				    JOIN tbtipocomissao ON (tbcomissao.idTipoComissao = tbtipocomissao.idTipoComissao)
				   WHERE idcomissao = '.$idCargo;
					
			$row = parent::select($select,FALSE);
		}
		
		return $row[0];	
		
	}
		
	###########################################################
	
	/**
	 * M�todo get_cargoComissaoPorId
	 * Informa o id e o nome do cargo em Comiss�o do Servidor (se tiver)
         * usado na rotina de Cargo em comiss�o para preencher a combo
	 * 
	 * @param	string $matricula  matricula do servidor
	 */

	function get_cargoComissaoPorId($id)
	
	{
            # Pega o nome do id do cargo em comiss�o
            $select ='SELECT tbtipocomissao.idTipoComissao,
                             CONCAT(tbtipocomissao.descricao," - ",tbtipocomissao.simbolo)
                        FROM tbcomissao 
                        JOIN tbtipocomissao ON (tbcomissao.idTipoComissao = tbtipocomissao.idTipoComissao)
                       WHERE idcomissao = '.$id;

            $row = parent::select($select,FALSE);
            return array($row[0],$row[1]);
	}
		
	###########################################################
	
	/**
	 * M�todo get_salarioCargoComissao
	 * Informa o sal�rio de um cargo em Comiss�o do Servidor (se tiver)
	 * 
	 * @param	string $matricula  matricula do servidor
	 */

	function get_salarioCargoComissao($matricula)
	
	{
		# Pega o id do cargo em comiss�o (se houver)		 
		$select = 'SELECT idComissao
			     FROM tbcomissao
			    WHERE dtExo is NULL AND matricula = '.$matricula;
					
		$row = parent::select($select,FALSE);
		$idCargo = $row[0];
		
		# Pega o sal�rio do id do cargo em comiss�o
		if (!is_null($idCargo))
		{
			$select ='SELECT tbtipocomissao.valsal 
						FROM tbcomissao 
						JOIN tbtipocomissao ON (tbcomissao.idTipoComissao = tbtipocomissao.idTipoComissao)
					   WHERE idcomissao = '.$idCargo;
					
			$row = parent::select($select,FALSE);
		}
		
		return $row[0];	
		
	}
		
	###########################################################
	
	/**
	 * M�todo get_trienioPercentual
	 * informa o percentual atual do trienio de uma matr�cula
	 * 
	 * @param	string $matricula matricula do servidor
	 */

	function get_trienioPercentual($matricula)
	{
		$select = 'SELECT percentual
		             FROM tbtrienio
		            WHERE matricula = '.$matricula.'
				 ORDER BY percentual desc';
		
		$row = parent::select($select,FALSE);
		
		return $row[0];
					
	}
	
	###########################################################
	
	/**
	 * M�todo get_trienioValor
	 * informa o valor atual do trienio de uma matr�cula
	 * 
	 * @param	string $matricula matricula do servidor
	 */

	function get_trienioValor($matricula)
	{
		$salario = $this->get_salarioBase($matricula);
		$percentual = $this->get_trienioPercentual($matricula);
		
		$valor = $salario * ($percentual/100);
		
		return $valor;
					
	}
	
	###########################################################
	
	/**
	 * M�todo get_trienioDataInicial
	 * informa a data Inicial de um trienio de uma matr�cula
	 * 
	 * @param	string $matricula matricula do servidor
	 */

	function get_trienioDataInicial($matricula)
	{
		$select = 'SELECT dtInicial
		             FROM tbtrienio
		            WHERE matricula = '.$matricula.'
				 ORDER BY percentual desc';
		
		$row = parent::select($select,FALSE);
		
		return date_to_php($row[0]);
					
	}
	
	###########################################################
	
	/**
	 * Método get_trienioDataProximoTrienio
	 * informa a data do próximo triênio a contar dda última data de triênio recebido
         * 
         * somente pega-se a data do triênio Inicial
	 * 
	 * @param	string $matricula matricula do servidor
	 */

	function get_trienioDataProximoTrienio($matricula)
	{
		$select = 'SELECT dtInicial
		             FROM tbtrienio
		            WHERE matricula = '.$matricula.'
				 ORDER BY percentual desc';
		
		$row = parent::select($select,FALSE);
                $dataTrienio = date_to_php($row[0]);
                
                $dataProximo = addAnos($dataTrienio, 3);  //Soma 3 anos ao último triênio recebido
		
		return $dataProximo;
	}
	
	###########################################################
	
	/**
	 * M�todo get_trienioPer�odoAquisitivo
	 * informa a per�odo Aquisitivo de um trienio de uma matr�cula
	 * 
	 * @param	string $matricula matricula do servidor
	 */

	function get_trienioPeriodoAquisitivo($matricula)
	{
		$select = 'SELECT dtInicioPeriodo,
		                  dtFimPeriodo
		             FROM tbtrienio
		            WHERE matricula = '.$matricula.'
				 ORDER BY percentual desc';
		
		$row = parent::select($select,FALSE);
		
		return date_to_php($row[0]).' - '.date_to_php($row[1]);
					
	}
	
        ###########################################################
	
	/**
	 * M�todo get_trienioNumProcesso
	 * informa a n� do processo de um trienio de uma matr�cula
	 * 
	 * @param	string $matricula matricula do servidor
	 */

	function get_trienioNumProcesso($matricula)
	{
		$select = 'SELECT numProcesso
		             FROM tbtrienio
		            WHERE matricula = '.$matricula.'
				 ORDER BY percentual desc';
		
		$row = parent::select($select,FALSE);
		
		return $row[0];
					
	}
	
	###########################################################
	
	/**
	 * Método get_trienioPublicacao
	 * informa data e página da publicação no DOERJ do triênio vigente (último)
	 * 
	 * @param	string $matricula matricula do servidor
	 */

	function get_trienioPublicacao($matricula)
	{
		$select = 'SELECT dtPublicacao,
		                  pgPublicacao
		             FROM tbtrienio
		            WHERE matricula = '.$matricula.'
				 ORDER BY percentual desc';
		
		$row = parent::select($select,FALSE);
		
		return date_to_php($row[0]).' - pág.'.$row[1];
					
	}
	
        ###########################################################

	/**
	 * M�todo get_nomelotacao
	 * Informa o nome da lota��o a partir do id
	 *
	 * @param	string $id  id da lota��o
	 */

	public function get_nomelotacao($id)

	{
		if (!is_numeric($id))
                    return $id;
                else
                {
                    $select = 'SELECT  concat(UADM,"-",DIR,"-",GER) as lotacao
                                 FROM tblotacao
                                WHERE idLotacao = '.$id;

                    $row = parent::select($select,FALSE);
                    return $row[0];
                }

	}

	###########################################################
	
	/**
	 * M�todo get_servidoresCargo
	 * 
	 * Exibe o n�mero de servidores ativos em um determinado cargo
	 */
	
	public function get_servidoresCargo($id)
	{
            $select = 'SELECT matricula                             
                         FROM tbfuncionario
                        WHERE Sit = 1 AND 
                              idCargo = '.$id;
           
            $numero = parent::count($select);
            return $numero;
	}

	###########################################################
	
	/**
	 * M�todo get_servidoresCargoComissao
	 * 
	 * Exibe o n�mero de servidores ativos em um determinado cargo em comissao
	 */
	
	public function get_servidoresCargoComissao($id)
	{
            $select = 'SELECT tbfuncionario.matricula                             
                         FROM tbfuncionario JOIN tbcomissao ON (tbfuncionario.matricula = tbcomissao.matricula)
                        WHERE Sit = 1
                          AND dtExo is NULL
                          AND tbcomissao.idTipoComissao = '.$id;
           
            $numero = parent::count($select);
            return $numero;
	}

	###########################################################
	
	/**
	 * M�todo get_cargoComissaoVagas
	 * 
	 * Exibe o n�mero de vagas em um determinado cargo em comissao
	 */
	
	public function get_cargoComissaoVagas($id)
	{
            $select = 'SELECT vagas                             
                         FROM tbtipocomissao 
                        WHERE idTipoComissao = '.$id;
           
            $row = parent::select($select,FALSE);		
            return $row[0];
	}

	###########################################################
	
	/**
	 * M�todo get_cargoComissaoVagas
	 * 
	 * Exibe o n�mero de vagas n�o ocupadas em um determinado cargo em comissao
	 */
	
	public function get_cargoComissaoVagasDisponiveis($id)
	{
            $vagas = $this->get_cargoComissaoVagas($id);
            $ocupadas = $this->get_servidoresCargoComissao($id);
            $disponiveis = $vagas - $ocupadas;
           
            return $disponiveis;
	}

	###########################################################
	
	/**
	 * M�todo get_servidoresConcurso
	 * 
	 * Exibe o n�mero de servidores ativos em um determinado concurso
	 */
	
	public function get_servidoresConcurso($id)
	{
            $select = 'SELECT matricula                             
                         FROM tbfuncionario
                        WHERE Sit = 1 AND 
                              idConcurso = '.$id;
           
            $numero = parent::count($select);
            return $numero;
	}

	###########################################################
	
	/**
	 * M�todo get_servidoresPerfil
	 * 
	 * Exibe o n�mero de servidores ativos em um determinado concurso
	 */
	
	public function get_servidoresPerfil($id)
	{
            $select = 'SELECT matricula                             
                         FROM tbfuncionario
                        WHERE Sit = 1 AND 
                              idPerfil = '.$id;
           
            $numero = parent::count($select);
            return $numero;
	}

	###########################################################
	
	/**
	 * M�todo get_servidoresSituacao
	 * 
	 * Exibe o n�mero de servidores ativos em um determinado concurso
	 */
	
	public function get_servidoresSituacao($id)
	{
            $select = 'SELECT matricula                             
                         FROM tbfuncionario
                        WHERE Sit = '.$id;
           
            $numero = parent::count($select);
            return $numero;
	}

	###########################################################
	
	/**
	 * M�todo get_servidoresSite
	 * 
	 * Exibe os servidores em cargo em comiss�o para o site da fenorte
         * 
         * a lista dos servidores ocupando os cargos em comiss�o que est�o 
         * marcados para exibi��o no site da Fenorte.
         * 
	 */
	
	public function get_servidoresSite()
	{
              $select = 'SELECT tbpessoa.nome as nome,                              
                                tbcomissao.descricao as cargo,
                                tbtipocomissao.idTipoComissao as ordem,
                                tbtipocomissao.exibeSite as exibicao,
                                tblotacao.UADM as uadm,
                                tblotacao.nome as lotacao,
                                tblotacao.ramais as ramais,
                                tblotacao.email as email
                            FROM tbfuncionario LEFT JOIN tbpessoa ON (tbfuncionario.idPessoa = tbpessoa.idPessoa)
                                                JOIN tbhistlot ON (tbfuncionario.matricula = tbhistlot.matricula)
                                                JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                        LEFT JOIN tbsituacao ON (tbfuncionario.sit = tbsituacao.idSit)
                                        LEFT JOIN tbperfil ON (tbfuncionario.idPerfil = tbperfil.idPerfil)
                                        LEFT JOIN tbcargo ON (tbfuncionario.idCargo = tbcargo.idCargo)
                                        LEFT JOIN tbcomissao ON (tbfuncionario.matricula = tbcomissao.matricula)
                                        LEFT JOIN tbtipocomissao ON (tbcomissao.idTipoComissao = tbtipocomissao.idTipoComissao)
                            WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.matricula = tbfuncionario.matricula)
                                AND tbcomissao.dtExo is NULL
                                AND tbtipocomissao.exibeSite
                                AND tbfuncionario.Sit = 1
                                ORDER BY tblotacao.UADM,ordem,tbpessoa.nome';
                        
            $row = parent::select($select);
            return $row;
	}

	###########################################################
	
	/**
	 * M�todo get_perfilLicenca
	 * informa se o perfil � permitido tirar licen�a
	 * 
	 * @param   integer $id id do Perfil
	 */

	function get_perfilLicenca($id)
	{
		$select = 'SELECT licenca
                             FROM tbperfil
                            WHERE idPerfil = '.$id;
		
		$row = parent::select($select,FALSE);
		
		return $row[0];
					
	}
	
	###########################################################
	
	/**
	 * M�todo get_perfilProgressao
	 * informa se o perfil � permitido ter progressao
	 * 
	 * @param   integer $id id do Perfil
	 */

	function get_perfilProgressao($id)
	{
		$select = 'SELECT progressao
                             FROM tbperfil
                            WHERE idPerfil = '.$id;
		
		$row = parent::select($select,FALSE);
		
		return $row[0];
					
	}
	
	###########################################################
	
	/**
	 * M�todo get_perfilTrienio
	 * informa se o perfil � permitido ter Tri�nios
	 * 
	 * @param   integer $id id do Perfil
	 */

	function get_perfilTrienio($id)
	{
		$select = 'SELECT trienio
                             FROM tbperfil
                            WHERE idPerfil = '.$id;
		
		$row = parent::select($select,FALSE);
		
		return $row[0];
					
	}
	
	###########################################################
	
	/**
	 * M�todo get_perfilComissao
	 * informa se o perfil � permitido ter Cargo em Comiss�o
	 * 
	 * @param   integer $id id do Perfil
	 */

	function get_perfilComissao($id)
	{
		$select = 'SELECT comissao
                             FROM tbperfil
                            WHERE idPerfil = '.$id;
		
		$row = parent::select($select,FALSE);
		
		return $row[0];
					
	}
	
	###########################################################
	
	/**
	 * M�todo get_perfilMatricula
	 * informa a matr�cula inicial e final permitida a um perfil
	 * 
	 * @param   integer $id id do Perfil
	 */

	function get_perfilMatricula($id)
	{
		$select = 'SELECT matIni,
                                  matFim
                             FROM tbperfil
                            WHERE idPerfil = '.$id;
		
		$row = parent::select($select,FALSE);
		
		return $row;
					
	}
	
	###########################################################
	
	/**
	 * M�todo get_perfilGratificacao
	 * informa se o perfil � permitido ter Gratifica��o Especial
	 * 
	 * @param   integer $id id do Perfil
	 */

	public function get_perfilGratificacao($id)
	{
		$select = 'SELECT gratificacao
                             FROM tbperfil
                            WHERE idPerfil = '.$id;
		
		$row = parent::select($select,FALSE);
		
		return $row[0];
					
	}
	
	###########################################################
	
	/**
	 * M�todo get_perfilQuantidade
	 * informa se o n�mero de servidores ativos nesse perfil
	 * 
	 * @param   integer $id id do Perfil
	 */

	public function get_perfilQuantidade($id)
	{
		$select = 'SELECT matricula
                             FROM tbfuncionario
                            WHERE Sit = 1
                              AND idPerfil = '.$id;
		
		$count = parent::count($select);
		
		return $count;
					
	}
	
	###########################################################
	
	/**
	 * M�todo get_perfilFerias
	 * informa se o perfil � permitido ter F�rias
	 * 
	 * @param   integer $id id do Perfil
	 */

	public function get_perfilFerias($id)
	{
		$select = 'SELECT ferias
                             FROM tbperfil
                            WHERE idPerfil = '.$id;
		
		$row = parent::select($select,FALSE);
		
		return $row[0];
					
	}
	
	###########################################################
	
	/**
	 * M�todo get_nivelCargo
	 * Informa o n�vel de escolaridade do cargo de uma matr�cula
         * 
	 * @param   string $matricula do servidor
	 */

        public function get_nivelCargo($matricula)
	{
            $select = 'SELECT tbcargo.tpCargo
			 FROM tbcargo JOIN tbfuncionario ON(tbfuncionario.idCargo = tbcargo.idCargo)
                        WHERE matricula = '.$matricula;
		
            $row = parent::select($select,FALSE);
		
            return $row[0];
        }
        
        ###########################################################
	
        public function get_telefones($matricula)
        
	/**	
	 * Informa os telefones de um servidor
         * 
	 * @param   string $matricula do servidor
	 */

        
	{
            $select = 'SELECT tbcontatos.numero
	                 FROM tbcontatos 
                    LEFT JOIN tbpessoa on tbcontatos.idPessoa = tbpessoa.idPessoa
	            LEFT JOIN tbfuncionario on tbpessoa.idPessoa = tbfuncionario.idPessoa
	                WHERE tbcontatos.tipo <> "E-mail" 
                          AND tbfuncionario.matricula = '.$matricula;		
		
            $row = parent::select($select);            
            $numTelefones = parent::count($select)-1;
            $telefone = NULL;
            
            # percorre o array
            foreach($row as $campo)
            {
                $telefone .= $campo[0];
                if ($numTelefones > 0)
                        $telefone.= ' - ';
                $numTelefones -=1;	
            }
	
	return $telefone;	
        }
        
        ##########################################################################################

        public function mudaStatusFeriasConfirmadaFruida()        
        
	/**	
	 * Fun��o que procura no banco de dados as f�rias 
         * que foram confirmadas cuja data inicial j� passou
	 * e muda para fru�da
	 */

        { 
            # monta o update
            $sql = 'UPDATE tbferias SET status = "fruida"
                     WHERE status = "confirmada"
                       AND dtInicial < current_date()';
            
            # executa
            parent::update($sql);

        }
        
        ##########################################################################################

        public function mudaStatusFeriasSolicitadaConfirmada($idFerias)        
        
	/**	
	 * Fun��o que muda uma f�rias espec�fica de Solicitada para Confirmada
	 */

        { 
            # monta o update
            $sql = 'UPDATE tbferias SET status = "confirmada"
                     WHERE idFerias = '.$idFerias;
            
            # executa
            parent::update($sql);

        }
        
        ###########################################################
	
	function get_idDbv($matricula)
	
	/**
	 *
	 * informa o id da tabela tddbv
	 * 
	 * @param	string $matricula matricula do servidor
	 */


	{
		$select = 'SELECT idDbv
                             FROM tbdbv
                            WHERE matricula = '.$matricula;
		
		$row = parent::select($select,FALSE);
                $count = parent::count($select);
                
                if($count == 0)
                    return NULL;
                else
                    return $row[0];
	}
	
	###########################################################
	
	function get_dbvAcumulacao($matricula)
	
	/**
	 *
	 * informa se o servidor tem ou n�o cargo acumulado
	 * 
	 * @param	string $matricula matricula do servidor
	 */


	{
		$select = 'SELECT acumulacao
                             FROM tbdbv
                            WHERE matricula = '.$matricula;
		
		$row = parent::select($select,FALSE);
                $count = parent::count($select);
                
                if($count == 0)
                    return NULL;
                else
                    return $row[0];
	}
	
	###########################################################
	
	function get_dbvAnoBase($matricula)
	
	/**
	 *
	 * informa o ano base da dbv
	 * 
	 * @param	string $matricula matricula do servidor
	 */


	{
		$select = 'SELECT anoBase
                             FROM tbdbv
                            WHERE matricula = '.$matricula;
		
		$row = parent::select($select,FALSE);
                $count = parent::count($select);
                
                if($count == 0)
                    return NULL;
                else
                    return $row[0];
	}
	
	###########################################################
	
	function get_folgasConcedidas($matricula)
	
	/**
	 * informa a quantidade de dias de folga que o servidor tem direito
	 * 
	 * @param	string $matricula matricula do servidor
	 */


	{
		$select = 'SELECT IFNULL(sum(folgas),0)
                             FROM tbfolgatre
                            WHERE matricula = '.$matricula;
		
		$row = parent::select($select,FALSE);
                $count = parent::count($select);
                
                if($count == 0)
                    return '0';
                else
                    return $row[0];
	}
	
	###########################################################
	
	function get_folgasFruidas($matricula)
	
	/**
	 * informa a quantidade de folga que o servidor fruiu (tirou)
	 * 
	 * @param	string $matricula matricula do servidor
	 */


	{
		$select = 'SELECT IFNULL(sum(dias), 0)
                             FROM tbfolga
                            WHERE matricula = '.$matricula;
		
		$row = parent::select($select,FALSE);
                $count = parent::count($select);
                
                if($count == 0)
                    return '0';
                else
                    return $row[0];
	}
	
	###########################################################
	
	function get_existeMatricula($matricula)
	
	/**
	 * informa se a matricula informada existe no cadastro
	 * 
	 * @param	string $matricula matricula do servidor
	 */


	{
		$select = 'SELECT matricula
                             FROM tbfuncionario
                            WHERE matricula = '.$matricula;		
		
                $count = parent::count($select);
                
                if($count == 0)
                    return FALSE;
                else
                    return TRUE;
	}
	
	###########################################################
	
	function get_novaMatricula($perfil)
	
	/**
	 * informa uma matr�cula nova (gera nova matr�cula)
	 * 
	 * @param	string $perfil perfil para saber a faixa da matr�cula
	 */
        
	{
            # pega a faixa da matr�cula para esse perfil
            $faixa = $this->get_perfilMatricula($perfil);
            
            # pega a �ltima matr�cula utilizada nessa faixa
            $select = 'SELECT matricula
                         FROM tbfuncionario
                        WHERE matricula >= '.$faixa[0].'
                          AND matricula <= '.$faixa[1].'  
                     ORDER BY matricula desc';		

            $row = parent::select($select,FALSE);
            $count = parent::count($select);            

            # se n�o tiver nenhum matricula cadastrada nessa faixa pega-se a matr�cula inicial da faixa                
            if($count == 0)
                $novaMatricula = $faixa[0];
            else
            {
                # pega a �ltima matr�cula
                $ultimaMatricula = $row[0];
                $novaMatricula = $ultimaMatricula +1;
            }

            return $novaMatricula;
	}
	
	###########################################################
	
	/**
	 * M�todo get_nivelCargoCargo
	 * Informa o n�vel de escolaridade do cargo de um idCargo
         * 
	 * @param   string $idCargo
	 */

        public function get_nivelCargoCargo($idCargo)
	{
            $select = 'SELECT tpCargo
			 FROM tbcargo
                        WHERE idCargo = '.$idCargo;
		
            $row = parent::select($select,FALSE);
		
            return $row[0];
        }
        
        ###########################################################
	
	/**
	 * M�todo get_planoCargosAtual
	 * Informa o id do Plano de CArgos Atual (com a data de publica��o mais recente)
         * 	 
	 */

        public function get_planoCargosAtual()
	{
            $select = 'SELECT idPlano
			 FROM tbplano
                     ORDER BY dtPublicacao desc';
		
            $row = parent::select($select,FALSE);
		
            return $row[0];
        }
        
        ###########################################################
	
	/**
	 * M�todo get_classeInicial
	 * Informa o idClasse inicial (sal�rio Inicial)
         * 
         * @param   string $plano
         * @param   string $nivel 
         * 	 
	 */

        public function get_classeInicial($plano,$nivel)
	{
            $select = 'SELECT idClasse
			 FROM tbclasse
                        WHERE idPlano = '.$plano.'
                          AND nivel = "'.$nivel.'"
                     ORDER BY valor';
		
            $row = parent::select($select,FALSE);
		
            return $row[0];
        }
        
        ###########################################################
	
	/**
	 * M�todo get_Pis
	 * fornece o Pis de um id_pessoa
	 * 
	 * @param	string $idPessoa do servidor
	 */

	public function get_Pis($idPessoa)
	{
		$select = 'SELECT pisPasep
                             FROM tbdocumentacao
                            WHERE idPessoa = '.$idPessoa;
		
		$idPessoa = parent::select($select,FALSE);
				
		return $idPessoa[0];
                
	}
	
	###########################################################
	
	/**
	 * M�todo get_perfilNome
	 * informa o nome do perfil
	 * 
	 * @param   integer $id id do Perfil
	 */

	public function get_perfilNome($id)
	{
		$select = 'SELECT nome
                             FROM tbperfil
                            WHERE idPerfil = '.$id;
		
		$row = parent::select($select,FALSE);
		
		return $row[0];					
	}
	
	###########################################################

	/**
	 * M�todo get_nomeCargo
	 * Informa o nome do cargo a partir do id
	 *
	 * @param	string $id  id do cargo
	 */

	public function get_nomeCargo($id)

	{
		if (!is_numeric($id))
                    return $id;
                else
                {
                    $select = 'SELECT  nome
                                 FROM tbcargo
                                WHERE idCargo = '.$id;

                    $row = parent::select($select,FALSE);
                    return $row[0];
                }

	}

	###########################################################
	
	function get_numDependentes($idPessoa)
	
	/**
	 * informa o n�mero de dependentes de um idPessoa
	 * 
	 * @param	string $idPessoa do servidor
	 */


	{
		$select = 'SELECT idDependente
                             FROM tbdependente
                            WHERE idPessoa = '.$idPessoa;		
		
                $count = parent::count($select);
                
                return $count;
	}
	
	###########################################################
	
	/**
	 * M�todo get_ultimoAcesso
	 * informa a data do �ltimo acesso a �rea do servidor de uma matr�cula
	 * 
	 * @param	string $matricula matricula do servidor
	 */

	function get_ultimoAcesso($matricula)
        {
                $select = 'SELECT date(ult_acesso)
                             FROM tbfuncionario
                            WHERE matricula = '.$matricula;

                # verifica se a matricula foi informada
                if(is_null($matricula))
                    $data[0] = "1900-01-01";
                else
                {
                    $data = parent::select($select,FALSE);
                }

                return $data[0];
        }
	
	##########################################################################################

	function aniversariante($matricula)
	
	
	# Fun��o que informa se a matricula est� fazendo anivers�rio na data atual
	#
	# Par�metro: a matr�cula a ser pesquisada
	
	{
                # Pega a data de nascimento		
		$select = "SELECT date_format(tbpessoa.dtNasc,'%d/%m')
		             FROM tbpessoa JOIN tbfuncionario ON(tbfuncionario.idPessoa = tbpessoa.idPessoa)
                            WHERE matricula = '$matricula'";
				
		$row = parent::select($select,FALSE);
                
                # Divide a data 
                $data = explode('/',$row[0]); 
                $d = $data[0];   // dia
                $m = $data[1];   // m�s
                
                # Verifica se � hoje o anivers�rio
                if ((intval(date('d')) == intval($d)) AND (intval(date('m')) == intval($m)))
                    return 1;
                else
                    return 0;
	
	}
	
	###########################################################
	
        public function get_dataVencimentoCarteiraMotorista($idPessoa)
                
	/**	 
	 * fornece a data de vencimento da carteira de Motorista de um idPessoa
	 * 
	 * @param	string $idPessoa do servidor
	 */

	
	{
		$select = 'SELECT date_format(dtVencMotorista,"%d/%m/%Y")
                             FROM tbdocumentacao
                            WHERE idPessoa = '.$idPessoa;
		
		$idPessoa = parent::select($select,FALSE);
				
		return $idPessoa[0];
                
	}
	
	###########################################################

	public function get_idPlanoAtual()
	
        /**
	 * 
	 * Informa o id do plano que est� ativo na tabela tbplano
	 * 
	 */
	
	{
            # Pega o cargo do servidor
            $select = 'SELECT idPlano
                         FROM tbplano
                        WHERE planoAtual = 1';

            $row = parent::select($select,FALSE);
            $id = $row[0];

            return $id;
			
	}
		
	###########################################################

	public function get_numDecretoPlanoAtual()
	
        /**
	 * 
	 * Informa o id do plano que est� ativo na tabela tbplano
	 * 
	 */
	
	{
            # Pega o cargo do servidor
            $select = 'SELECT numDecreto
                         FROM tbplano
                        WHERE planoAtual = 1';

            $row = parent::select($select,FALSE);
            $id = $row[0];

            return $id;
			
	}
		
    ##########################################################################################

    function get_dadosDiaria($iddiaria)
    
     /**
      * 
      * Retorna dados de uma di�ria
      * 
      * @param $iddiaria integer o id da diaria
      * 
      */
            
    {
        # Monta o select
        $select = "SELECT date_format(dataCi,'%d/%m/%Y'),
                          numeroCi,
                          assuntoCi,
                          valor
                     FROM tbdiaria
                    WHERE iddiaria = '$iddiaria'";
        
        $row = parent::select($select,FALSE);
        return $row;
    }

   ###########################################################

    /**
     * M�todo get_parentesco
     * Informa o parentesco
     * 
     * @param	string $id  id do parentesco
     */

    public function get_parentesco($id)

    {
            $select = 'SELECT  parentesco
                          FROM tbparentesco
                         WHERE idparentesco = '.$id;

            $row = parent::select($select,FALSE);

            return $row[0];

    }

    ########################################################### 

    function get_licencaPremioNumDiasPublicadaPorMatricula($matricula)

    /**
     * informa o n�mero de dias publicados para licen�a pr�mio
     * 
     * @param	string $matricula matricula do servidor
     */

    {
                   
        # Pega quantos dias foram publicados
        $select = 'SELECT SUM(numDias) 
                     FROM tbpublicacaopremio 
                    WHERE matricula = '.$matricula;

        $row = parent::select($select,FALSE);     

        if (is_null($row[0]))
            return 0;
        else 
            return $row[0];
    }

    ########################################################### 

    function get_licencaPremioNumDiasPublicadaPorId($idPublicacaoPremio)

    /**
     * informa o n�mero de dias publicados para licen�a pr�mio de uma publica��o espec�fica
     * 
     * @param	string $idPublicacaoPremio id da Publica��o
     */

    {
                   
        # Pega quantos dias foram publicados
        $select = 'SELECT numDias 
                     FROM tbpublicacaopremio 
                    WHERE idPublicacaoPremio = '.$idPublicacaoPremio;

        $row = parent::select($select,FALSE);     

        if (is_null($row[0]))
            return 0;
        else 
            return $row[0];
    }

    ########################################################### 

    function get_licencaPremioNumDiasFruidasPorId($idPublicacaoPremio)

    /**
     * informa o número de dias fru�dos para licen�a pr�mio de uma publicação espec�fica
     * 
     * @param	string $idPublicacaoPremio id da Publica��o
     */

    {
                   
        # Pega quantos dias foram publicados
        $select = 'SELECT SUM(numDias) 
                     FROM tblicenca 
                    WHERE idPublicacaoPremio = '.$idPublicacaoPremio;

        $row = parent::select($select,FALSE);     

        if (is_null($row[0]))
            return 0;
        else 
            return $row[0];
    }

    ########################################################### 

    function get_licencaPremioNumDiasDisponiveisPorId($idPublicacaoPremio)

    /**
     * informa o n�mero de dias Dispon�veis para licen�a pr�mio de uma publica��o espec�fica
     * 
     * @param	string $idPublicacaoPremio id da Publica��o
     */

    {
                   
        # Pega quantos dias foram publicados
        $diasPublicados = $this->get_licencaPremioNumDiasPublicadaPorId($idPublicacaoPremio);
        
        # Pega os dias fru�dos
        $diasFruidos = $this->get_licencaPremioNumDiasFruidasPorId($idPublicacaoPremio);
        
        # Calcula os dias dispon�veis dessa publica��o
        $diasDisponiveis = $diasPublicados-$diasFruidos;
        return $diasDisponiveis;
    }

    ###########################################################    
    
    function get_licencaPremioNumDiasFruidos($matricula)

    /**
     * informa a quantidade de dias de Licen�a Pr�mio que o servidor j� fruiu
     * 
     * @param	string $matricula matricula do servidor
     */

    {
        # Pega quantos dias o servidor tirou de licen�a at� hoje
        # considerando que o id da licen�a pr�mio � 6
        $select = 'SELECT SUM(numDias) 
                     FROM tblicenca 
                    WHERE idTpLicenca = 6
                    AND matricula = '.$matricula;

        $row = parent::select($select,FALSE);   // Total de dias tirados de licen�a premioa

        if (is_null($row[0]))
            return 0;
        else 
            return $row[0];
    }

    ##########################################################################################

    function get_licencaPremioPublicacao($matricula)

    # Fun��o que informa as publica��es para licen�a pr�mio de uma matricula
    #
    # Par�metro: matricula do servidor

    {
        # valida par�metro
        if(is_null($matricula))
            return FALSE;

        # Pega as publica��es de licen�a pr�mio dessa matricula
        $select = 'SELECT idPublicacaoPremio,
                          CONCAT(date_format(dtPublicacao,"%d/%m/%Y")," - Periodo Aquisitivo (",date_format(dtInicioPeriodo,"%d/%m/%Y"),"-",date_format(dtFimPeriodo,"%d/%m/%Y"),")")
                     FROM tbpublicacaopremio
                    WHERE matricula = '.$matricula.'
                        ORDER BY dtPublicacao';            

        $row = parent::select($select);

        if (parent::count($select) > 0)
            return $row;
        else
            return NULL;
    }

    ##########################################################################################

    function get_licencaPremioPublicacaoDisponivel($matricula)

    # Fun��o que informa a publica��o com dias dispon�veis para licen�a pr�mio de uma matricula
    #
    # Par�metro: matricula do servidor

    {
        # vari�veis
        $publicacaoEscolhida = NULL;    // guarda a publica��o escolhida para retornar
        $diasDisponiveis = NULL;        // guarda a quantidade de dias disponiveis pela publica��o

        # valida par�metro
        if(is_null($matricula))
            return FALSE;

        # Pega as publica��es de licen�a pr�mio dessa matricula
        $select = 'SELECT idPublicacaoPremio,
                          CONCAT(date_format(dtPublicacao,"%d/%m/%Y")," - Periodo Aquisitivo (",date_format(dtInicioPeriodo,"%d/%m/%Y"),"-",date_format(dtFimPeriodo,"%d/%m/%Y"),")")
                     FROM tbpublicacaopremio
                    WHERE matricula = '.$matricula.'
                        ORDER BY dtInicioPeriodo'; 

        $row = parent::select($select);
        if (parent::count($select) > 0)
        {
            # Percorre o array para verificar a primeira publica��o com dias dispon�veis
            foreach ($row as $publicacao)
            {
                $diasDisponiveis = $this->get_licencaPremioNumDiasDisponiveisPorId($publicacao[0]);
                if($diasDisponiveis > 0)
                {
                    $publicacaoEscolhida = array(array($publicacao[0],$publicacao[1]));
                    break;
                }
            }
        }
        return $publicacaoEscolhida;		

    }

    ##########################################################################################

    function get_licencaPremioDadosPublicacao($idpublicacaopremio)

    # Fun��o que informa, de uma s� vez, v�rios dados de uma 
    # publica��o de licen�a pr�mio de uma matricula para grava��o
    # na rotina extra de cadastro de licen�a
    #
    # Par�metro: id da publica��o

    {
            # valida par�metro
            if(is_null($idpublicacaopremio))
                return FALSE;

            # Monta o select		
            $select = 'SELECT dtInicioPeriodo,
                              dtFimPeriodo,
                              processo,
                              dtPublicacao,
                              pgPublicacao                              
                         FROM tbpublicacaopremio
                        WHERE idpublicacaopremio = '.$idpublicacaopremio;

            $row = parent::select($select,FALSE);
            return $row;		

    }

    ##########################################################################################

    function get_licencaPremioNumPublicacao($idPublicacao)

    # Fun��o que informa quantas licen�as foram lan�adas com essa publica��o
    #
    # Par�metro: id da Publica��o

    {
            # valida par�metro
            if(is_null($idPublicacao))
                return FALSE;

            # Monta o select		
            $select = 'SELECT idLicenca
                         FROM tbLicenca
                        WHERE idPublicacaoPremio = '.$idPublicacao;

            $row = parent::count($select);

            return $row;		

    }

    ##########################################################################################

    function get_licencaPremioNumProcesso($matricula)

    # Fun��o que informa o N�mero do processo da �ltima publica��o
    # para sugerir para o pr�ximo cadastro de publica��es
    #
    # Par�metro: matricula do servidor

    {
        # valida par�metro
        if(is_null($matricula))
            return FALSE;

        # Pega as publica��es de licen�a pr�mio dessa matricula
        $select = 'SELECT processo
                     FROM tbpublicacaopremio
                    WHERE matricula = '.$matricula.'
                 ORDER BY dtPublicacao DESC';            

        $row = parent::select($select,FALSE);

        return $row[0];		

    }

    ##########################################################################################

    function get_licencaPremioNumProcessoPorId($idProcesso)

    # Fun��o que informa o N�mero do processo de uma Publica��o de Licen�a Pr�mio
    # para sugerir para o pr�ximo cadastro de Licen�a
    #
    # Par�metro: id da Publica��o

    {
        # valida par�metro
        if(is_null($idProcesso))
            return FALSE;

        # Pega as publica��es de licen�a pr�mio dessa matricula
        $select = 'SELECT processo
                     FROM tbpublicacaopremio
                    WHERE idPublicacaoPremio = '.$idProcesso;            

        $row = parent::select($select,FALSE);

        return $row[0];		

    }

    ##########################################################################################
    
    function get_licencaDados($idLicenca)


    # Fun��o que informa v�rios dados de uma licen�a
    #
    # Fun��o usada na rotina tempor�rio que transforma uma licen�a em publica��o de licen�a premio
    # 
    # Par�metro: id da licen�a

    {
            # valida par�metro
            if(is_null($idLicenca))
                return FALSE;

            # Monta o select		
            $select = 'SELECT processo,
                              dtInicioPeriodo,
                              dtFimPeriodo,
                              dtPublicacao,
                              pgPublicacao,
                              obs,
                              matricula                              
                         FROM tblicenca
                        WHERE idLicenca = '.$idLicenca;

            $row = parent::select($select,FALSE);
            return $row;		

    }

    ###########################################################

    /**
     * M�todo get_cpf
     * fornece o CPF de um id_pessoa
     * 
     * @param	string $idPessoa do servidor
     */

    public function get_cpf($idPessoa)
    {
            $select = 'SELECT cpf
                         FROM tbdocumentacao
                        WHERE idPessoa = '.$idPessoa;

            $idPessoa = parent::select($select,FALSE);

            return $idPessoa[0];

    }

    ###########################################################

    /**
     * M�todo get_identidade
     * fornece o n�mero, org�o e data de emiss�o da
     * carteira de identidade de um id_pessoa
     * 
     * @param	string $idPessoa do servidor
     */

    public function get_identidade($idPessoa)
    {
            $select = 'SELECT CONCAT(identidade," - ",orgaoId," - ",DATE_FORMAT(dtId,"%d/%m/%Y"))
                         FROM tbdocumentacao
                        WHERE idPessoa = '.$idPessoa;

            $valor = parent::select($select,FALSE);

            return $valor[0];

    }

    ###########################################################

    public function get_feriado($data = NULL)
    /**
     * 
     * Retorna uma string com o nome do feriado
     * ou NULLo se n�o tiver feriado nessa data
     * 
     * @param date $data a data (no formato dia/m�s/ano) a ser pesquisada, se nulo pega a data atual
     * 
     */
    {
        if(is_null($data))
        {
            # Monta o select
            $select = 'SELECT descricao
                         FROM tbferiado 
                        WHERE (tipo = "anual" AND MONTH(data) = MONTH(current_date()) AND DAY(data) = DAY(current_date())
                           OR (tipo = "data �nica" and  data = current_date()))';
        }
        else
        {
            $data = date_to_bd($data);

            # Monta o select
            $select = 'SELECT descricao
                         FROM tbferiado 
                        WHERE (tipo = "anual" AND MONTH(data) = MONTH("'.$data.'") AND DAY(data) = DAY("'.$data.'")
                           OR (tipo = "data �nica" and  data = "'.$data.'"))';
        }
        
        $row = parent::select($select,FALSE);       
        return $row[0];
    }
	
    ##########################################################################################

    function emFolgaTre($matricula)

    # Fun��o que informa se a matricula est� folgando (TRE) na data atual
    #
    # Par�metro: a matr�cula a ser pesquisada

    {
        # Monta o select
        $select = "SELECT idFolga
                     FROM tbfolga
                    WHERE matricula = '$matricula'
                      AND current_date() >= data 
                      AND current_date() <= ADDDATE(data,dias-1)";

        $row = parent::select($select,FALSE);
        
        if(is_null($row[0]))
            return 0;
        else 
            return 1;
    }

    ##########################################################################################

    function emAfastamentoTre($matricula)

    # Função que informa se a matricula está afastada para o (TRE) na data atual
    #
    # Par�metro: a matr�cula a ser pesquisada

    {
        # Monta o select
        $select = "SELECT idFolgatre
                     FROM tbfolgatre
                    WHERE matricula = '$matricula'
                      AND current_date() >= data 
                      AND current_date() <= ADDDATE(data,dias-1)";

        $row = parent::select($select,FALSE);
        
        if(is_null($row[0]))
            return 0;
        else 
            return 1;
    }

    ###########################################################

	
	/**
	 * M�todo get_idFuncional
	 * Informa a idFuncional de um servidor
	 * 
	 * @param	string $matricula  matricula do servidor
	 */

	public function get_idFuncional($matricula)
	
	{
            # Pega o cargo do servidor
            $select = 'SELECT idFuncional
                         FROM tbfuncionario
                        WHERE matricula = '.$matricula;

            $row = parent::select($select,FALSE);
            
            return $row[0];
			
	}
		
    ###########################################################

	
	/**
	 * M�todo get_idServidor
	 * Informa a idServidor de uma matrícula
	 * 
	 * @param	string $matricula  matricula do servidor
	 */

	public function get_idServidor($matricula)
	
	{
            # Pega o cargo do servidor
            $select = 'SELECT idServidor
                         FROM tbservidor
                        WHERE matricula = '.$matricula;

            $row = parent::select($select,FALSE);
            
            return $row[0];
			
	}
		
###########################################################
	
    /**
     * Método get_tipoSenha
     * Informa o tipo da senha (padrão/bloqueada/Ok) 
     * 
     * @param	string $matricula	matricula do servidor
     */

    function get_tipoSenha($matricula)
    { 

        $select = "SELECT senha_intra		  
                     FROM tbfuncionario
                    WHERE matricula = ".$matricula;
        
        $pessoal = new Pessoal();
        $row = parent::select($select,FALSE);
        $padrao = MD5(SENHA_PADRAO);
        
        switch ($row[0])
        {
            # senha padrão
            case $padrao :
                return 1;
                break;
            
            # senha bloqueada
            case NULL :
                return 2;
                break;

            # senha ok
            default:
                return 3;
                break;
        }
    }

    ###########################################################
}
?>
