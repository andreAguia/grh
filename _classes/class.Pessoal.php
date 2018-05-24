<?php

class Pessoal extends Bd
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
    private $banco = "grh";
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
     * Método set_tabela
     * 
     * @param  	$nomeTabela	-> Nome da tabela do banco de dados intra que ser� utilizada
     */
    public function set_tabela($nomeTabela){
        $this->tabela = $nomeTabela;
    }

    ###########################################################

    /**
     * Método set_idCampo
     * 
     * @param  	$idCampo)	-> Nome do campo chave da tabela
     */
    public function set_idCampo($idCampo)
    {
        $this->idCampo = $idCampo;
    }

    ###########################################################

    /**
     * Método Gravar
     */
    public function gravar($campos = NULL,$valor = NULL,$idValor = NULL,$tabela = NULL,$idCampo = NULL,$alerta = FALSE){

        if(is_null($tabela)){
            $tabela = $this->tabela;
        }

        if(is_null($idCampo)){
            $idCampo = $this->idCampo;
        }

        parent::gravar($campos,$valor,$idValor,$tabela,$idCampo,$alerta);
    }

    ###########################################################

    /**
     * Método Excluir
     */
    public function excluir($idValor = NULL,$tabela = NULL,$idCampo = 'id'){

        # efetua a exclus�o
        parent::excluir($idValor,$this->tabela,$this->idCampo);

        return TRUE;		
    }

    ###########################################################

    /**
     * Método get_gratificacao
     * informa graificação de uma matrícula(se houver)
     * 
     * @param	string $idServidor idServidor do servidor
     */

    public function get_gratificacao($idServidor)
    {
            $select = 'SELECT valor
                         FROM tbgratificacao
                        WHERE idServidor = '.$idServidor.'
                          AND current_date() >= dtInicial 
                          AND (dtFinal is NULL OR current_date() <= dtFinal)';

            $row = parent::select($select,FALSE);

            return $row[0];

    }

    ###########################################################

    /**
     * Método get_gratificacaoDtFinal
     * informa a data de t�rmino da graificação de uma matrícula(se houver)
     * 
     * @param	string $idServidor idServidor do servidor
     */

    public function get_gratificacaoDtFinal($idServidor)
    {
        $select = 'SELECT dtFinal
                     FROM tbgratificacao
                    WHERE idServidor = '.$idServidor.'
                    ORDER BY dtInicial desc';
        $numero = parent::count($select);
        $row = parent::select($select,FALSE);


        # For�a como NULL caso seja em branco
        if((is_null($row[0])) OR ($row == ''))
            $row[0] = NULL;


        # Verifica se j� tem alguma graificação ou se nunca teve
        if($numero == 0)
            return FALSE; # nunca teve graificação
        else
            return $row[0]; # Informa se tem graificação em aberto
    }

    ###########################################################

    /**
     * Método get_periodoDisponivel
     * informa o per�odo dispon�vel de férias de um servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */

    public function get_periodoDisponivel($idServidor)
    {
            $select = "SELECT anoExercicio,
                      sum(numDias) as dias,
                      status
                 FROM tbferias
                WHERE idServidor = '$idServidor' 
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
     * Método get_ramais
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
     * Método get_salarioBase
     * informa o sal�rio base de uma matrícula
     * 
     * @param	string $idServidor idServidor do servidor
     */

    public function get_salarioBase($idServidor)
    {
            $select = 'SELECT tbclasse.valor
                         FROM tbprogressao, tbclasse
                         WHERE idServidor = '.$idServidor.'
                           AND tbprogressao.idClasse = tbclasse.idClasse
                  ORDER BY valor desc';

            $row = parent::select($select,FALSE);

            return $row[0];

    }

    ###########################################################

    /**
     * Método get_salarioTotal
     * informa o sal�rio Total de uma matrícula
     * 
     * @param	string $idServidor idServidor do servidor
     */

    public function get_salarioTotal($idServidor)
    {

        # Resumo financeira
        $salario = $this->get_salarioBase($idServidor);
        $trienio = $this->get_trienioValor($idServidor);
        $comissao = $this->get_salarioCargoComissao($idServidor);
        $gratificacao = $this->get_gratificacao($idServidor);
        $cessao = $this->get_salarioCessao($idServidor);
        $total = $salario+$trienio+$comissao+$gratificacao+$cessao;

        return $total;
    }

    ###########################################################

    /**
     * Método get_salarioCessao
     * informa o sal�rio recebido pelo �rg�o de origem de um cedido
     * 
     * @param	string $idServidor idServidor do servidor
     */

    public function get_salarioCessao($idServidor)
    {
            $select = 'SELECT salario
                         FROM tbcedido
                        WHERE idServidor = '.$idServidor;

            $row = parent::select($select,FALSE);

            return $row[0];

    }

    ###########################################################

    /**
     * Método get_aniversariantes
     * Exibe os aniversariantes de um determinado mês
     * 
     * @param	$mes	string	valor de 1 a 12 que informa o m�s
     */
    public function get_aniversariantes($mes = NULL){
        
        # Se o mês não for definido pega-se o mês atual
        if (is_null($mes)){
            $mes = date("n");
        }

        # Monta o select
        $select = 'SELECT concat(date_format(tbpessoa.dtNasc,"%d/%m")," - ",tbpessoa.nome) as nasc,
                          date_format(tbpessoa.dtNasc,"%d")	
                     FROM tbpessoa JOIN tbservidor USING (idPessoa)
                    WHERE month(dtNasc) = '.$mes.' 
                      AND tbservidor.situacao = 1
                 ORDER BY nasc';

        # Pega o resultado do select
        $result = parent::select($select);

        return $result; 
    }
    
    ###########################################################

    /**
     * Método get_numAniversariantes
     * Exibe os aniversariantes de um determinado mês
     * 
     * @param	$mes	string	valor de 1 a 12 que informa o m�s
     */
    public function get_numAniversariantes($mes = NULL){
        
        # Se o mês não for definido pega-se o mês atual
        if (is_null($mes)){
            $mes = date("n");
        }

        # Monta o select
        $select = 'SELECT idPessoa
                     FROM tbpessoa JOIN tbservidor USING (idPessoa)
                    WHERE month(dtNasc) = '.$mes.' 
                      AND tbservidor.situacao = 1';

        # Pega o resultado do select
        $result = parent::count($select);

        return $result; 
    }
    
    ###########################################################

    /**
     * Método get_numAniversariantesHoje
     * Exibe os aniversariantes de hoje
     */
    public function get_numAniversariantesHoje(){
        
        # Monta o select
        $select = 'SELECT idPessoa
                     FROM tbpessoa JOIN tbservidor USING (idPessoa)
                    WHERE (DAY(dtNasc) = DAY(CURDATE()) AND MONTH(dtNasc) = MONTH(CURDATE()))
                      AND tbservidor.situacao = 1';

        # Pega o resultado do select
        $result = parent::count($select);

        return $result; 
    }
    
    ###########################################################

    /**
     * Método set_senhaNull
     * muda a senha de um usu�rio para NULL (bloqueia o mesmo)
     * 
     * @param	string 	$idServidor 	-> idServidor do servidor
     * @param 	string	$senha		-> senha (não criptofrafada) a ser gravada (se nulo grava-se a senha padr�o)
     */
    public function set_senhaNull($matr,$alert = TRUE)
    {
            $senha = NULL;
            parent::gravar('senha_intra',$senha,$matr,'tbservidor','idServidor',$alert);

    }
    ###########################################################

    /**
     * Método get_diasAusentes
     * Informa, em dias, o per�odo entre a data atual
     * e o �ltimo acesso do usu�rio 
     *
     * @param	string $idServidor	idServidor do servidor
     */
    public function get_diasAusentes($idServidor)
    { 

            $select = "SELECT date_format(ult_acesso,'%d/%m/%Y')		  
                                     FROM tbservidor
                    WHERE idServidor = '$idServidor'";

            # Pega o resultado do select
            $result = parent::select($select,FALSE);

            $data_Inicial = $result[0];

            $diferenca = dataDif($data_Inicial);	# chama o m�todo est�tico dataDiff

            return $diferenca;


    }

    ######################################################################################

    /**
     * Método get_lotacao
     * Informa a lota��o atual do servidor
     * 
     * @param	string $idServidor  idServidor do servidor
     */

    public function get_lotacao($idServidor)

    {
            $select = 'SELECT  tblotacao.UADM,
                               tblotacao.DIR,
                               tblotacao.GER
                          FROM tbhistlot LEFT JOIN tblotacao on tbhistlot.lotacao = tblotacao.idlotacao
                         WHERE tbhistlot.idServidor = '.$idServidor.'
                         ORDER BY data DESC';

            $row = parent::select($select,FALSE);

            return $row[0].'-'.$row[1].'-'.$row[2];

    }

    ######################################################################################

    /**
     * Método get_lotacao
     * Informa a lotação atual do servidor sem o UADM
     * 
     * @param	string $idServidor  idServidor do servidor
     */

    public function get_lotacaoSimples($idServidor)

    {
            $select = 'SELECT  tblotacao.DIR,
                               tblotacao.GER
                          FROM tbhistlot LEFT JOIN tblotacao on tbhistlot.lotacao = tblotacao.idlotacao
                         WHERE tbhistlot.idServidor = '.$idServidor.'
                         ORDER BY data DESC';

            $row = parent::select($select,FALSE);

            return $row[0].'-'.$row[1];

    }

    ###########################################################

    /**
     * Método get_lotacaoNumServidores
     * Informa o n�mero de servidores ativos nessa lota��o
     * 
     * @param	string $idServidor  idServidor do servidor
     */

    public function get_lotacaoNumServidores($id)

    {
            $select = 'SELECT tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                          WHERE tbservidor.situacao = 1
                            AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                            AND tbhistlot.lotacao = '.$id;

            $numero = parent::count($select);
            return $numero;

    }

    ###########################################################

    /**
     * Método get_idlotacao
     * Informa o id da lotação atual do servidor
     *
     * @param	string $idServidor  id do servidor
     */

    public function get_idlotacao($idServidor)

    {
            $select = 'SELECT  tblotacao.idlotacao
                          FROM tbhistlot LEFT JOIN tblotacao on tbhistlot.lotacao = tblotacao.idlotacao
                         WHERE tbhistlot.idServidor = '.$idServidor.'
                         ORDER BY data DESC';

            $row = parent::select($select,FALSE);
            return $row[0];

    }

    ###########################################################


    /**
     * Método get_cargo
     * Informa o cargo do servidor
     * 
     * @param	string $idServidor  idServidor do servidor
     */

    public function get_cargo($idServidor)

    {
        # Pega o cargo do servidor
        $select = 'SELECT tbtipocargo.idTipoCargo,
                          tbtipocargo.sigla,
                          tbcargo.nome
                     FROM tbservidor LEFT JOIN tbcargo USING (idCargo)
                                     LEFT JOIN tbtipocargo USING (idTipoCargo)
                    WHERE idServidor = '.$idServidor;

        $row = parent::select($select,FALSE);

        if(($row[0] == 1)OR($row[0] == 2)){ // Se é professor
            $tipoCargo = NULL;
        }else{
            $tipoCargo = $row[1];      
        }

        $nomeCargo = $row[2];
        $comissao = $this->get_cargoComissao($idServidor);

        $retorno = NULL;

        if(!empty($tipoCargo)){
            $retorno = $tipoCargo;             
        }

        if(!empty($nomeCargo)){
            if(!empty($tipoCargo)){
                $retorno .= ' - '.$nomeCargo;
            }else{
                $retorno = $nomeCargo;
            }
        }

        if(!empty($comissao)){
             $retorno .= ' ('.$comissao.')'; 
        }
        return $retorno;
    }

    ###########################################################


    /**
     * Método get_perfil
     * Informa o perfil do servidor
     * 
     * @param   string $idServidor  idServidor do servidor
     */

    public function get_perfil($idServidor)

    {
            # Pega o cargo do servidor
            $select = 'SELECT tbperfil.nome
                         FROM tbservidor LEFT JOIN tbperfil ON (tbservidor.idPerfil=tbperfil.idPerfil)
                        WHERE idServidor = '.$idServidor;

            $row = parent::select($select,FALSE);
            $perfil = $row[0];

            return $perfil;

    }

    ###########################################################


    /**
     * Método get_idCargo
     * Informa o id do cargo do servidor
     * 
     * @param	string $idServidor  idServidor do servidor
     */

    public function get_idCargo($idServidor)

    {
            # Pega o cargo do servidor
            $select = 'SELECT tbcargo.idCargo
                         FROM tbservidor LEFT JOIN tbcargo ON (tbservidor.idCargo=tbcargo.idCargo)
                        WHERE idServidor = '.$idServidor;

            $row = parent::select($select,FALSE);
            $idcargo = $row[0];

            return $idcargo;
    }

    ###########################################################

    /**
     * Método get_idpessoa
     * fornece o id_pessoa de uma idServidor
     * 
     * @param	string $idServidor idServidor do servidor
     */

    public function get_idPessoa($idServidor)
    {
            $select = 'SELECT idPessoa
                         FROM tbservidor
                        WHERE idServidor = '.$idServidor;

            $id_pessoa = parent::select($select,FALSE);

            return $id_pessoa[0];
    }

    ###########################################################

    /**
     * Método get_idpessoaCPF
     * fornece o id_pessoa de um CPF
     * 
     * @param	string $cpf cpf do servidor
     */

    public function get_idPessoaCPF($cpf)
    {
            $select = 'SELECT idPessoa
                         FROM tbdocumentacao
                        WHERE cpf = "'.$cpf.'"';

            $idPessoa = parent::select($select,FALSE);

            return $idPessoa[0];                
    }

    ###########################################################

    /**
     * Método get_idPessoaPis
     * fornece o id_pessoa de um PisF
     * 
     * @param	string $pis do servidor
     */

    public function get_idPessoaPis($pis)
    {
            $select = 'SELECT idPessoa
                         FROM tbdocumentacao
                        WHERE pisPasep = "'.$pis.'"';

            $idPessoa = parent::select($select,FALSE);

            return $idPessoa[0];

    }

    ##########################################################

    /**
     * Método get_anoAdmissao
     * informa o ano de admiss�o de um servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */

    function get_anoAdmissao($idServidor)
    {
            $select = 'SELECT YEAR(dtAdmissao)
                                 FROM tbservidor
                                WHERE idServidor = '.$idServidor;

            $ano = parent::select($select,FALSE);

            return $ano[0];
    }

    ###########################################################

    /**
     * Método get_dtAdmissao
     * informa o ano de admiss�o de um servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */

    function get_dtAdmissao($idServidor)
    {
            $select = 'SELECT dtAdmissao
                         FROM tbservidor
                        WHERE idServidor = '.$idServidor;

            $dt = parent::select($select,FALSE);

            return date_to_php($dt[0]);
    }

    ###########################################################

    function get_idPerfil($idServidor)

    /**
     * Método get_idPerfil
     * informa o id do perfil do servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */


    {
            $select = 'SELECT idPerfil
                         FROM tbservidor
                        WHERE idServidor = '.$idServidor;

            $row = parent::select($select,FALSE);

            return $row[0];
    }

    ###########################################################

    function get_digito($idServidor)

    /**
     * Método get_digito
     * informa o d�gito verificador de uma matrícula
     * 
     * @param	string $idServidor idServidor do servidor
     */

    {

            $ndig = 0;


            switch (strlen($idServidor))
            {
                case 4:
                    $idServidor = "0".$idServidor;
                    break;
                case 3:
                    $idServidor = "00".$idServidor;
                    break;
                case 2:
                    $idServidor = "000".$idServidor;
                    break;
            }



            $npos = substr($idServidor,4,1);
            $npos = $npos * 2;
            if ($npos < 10) 
               $ndig = $ndig + $npos;
            else
               $ndig = $ndig + 1 + ($npos - 10);



            $npos = substr($idServidor,3,1);
            $ndig = $ndig + $npos;



            $npos = substr($idServidor,2,1);
            $npos = $npos * 2;
            if ($npos < 10)
               $ndig = $ndig + $npos;
            else
               $ndig = $ndig + 1 + ($npos - 10);



            $npos = substr($idServidor,1,1);
            $ndig = $ndig + $npos;



            $npos = substr($idServidor,0,1);
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

    function emFerias($idServidor)

    # Função que informa se a idServidor est� em férias na data atual
    #
    # Parâmetro: a matrícula a ser pesquisada

    {
        # Monta o select
        $select = "SELECT idFerias 
                     FROM tbferias
                    WHERE idServidor = '$idServidor'
                      AND current_date() >= dtInicial 
                      AND current_date() <= ADDDATE(dtInicial,numDias-1)";

        $row = parent::select($select,FALSE);

        if (is_null($row[0]))
            return 0;
        else 
            return 1;
    }

    ##########################################################################################

    function emLicenca($idServidor)


    # Função que informa se a idServidor está em licanca na data atual
    #
    # Parâmetro: a matrícula a ser pesquisada

    {
        # Monta o select
        $select = "SELECT idLicenca 
                     FROM tblicenca
                    WHERE idServidor = '$idServidor'
                      AND current_date() >= dtInicial 
                      AND current_date() <= ADDDATE(dtInicial,numDias-1)";

        $row = parent::select($select,FALSE);

        if (is_null($row[0]))
            return 0;
        else 
            return 1;
    }
    
    ##########################################################################################

    function emLicencaPremio($idServidor){


    # Função que informa se a idServidor está em licanca Prêmio na data atual
    #
    # Parâmetro: a matrícula a ser pesquisada
    
        # Monta o select
        $select = "SELECT idLicencaPremio 
                     FROM tblicencapremio
                    WHERE idServidor = '$idServidor'
                      AND current_date() >= dtInicial 
                      AND current_date() <= ADDDATE(dtInicial,numDias-1)";

        $row = parent::select($select,FALSE);

        if (is_null($row[0]))
            return 0;
        else 
            return 1;
    }
    
    ##########################################################################################
    
    function emCessao($idServidor)


    # Função que informa se o servidor está cedido para outro órgão
    #
    # Parâmetro: a matrícula a ser pesquisada

    {
        # Monta o select		
        $select = "SELECT orgao 
                     FROM tbhistcessao
                    WHERE idServidor = '$idServidor'
                      AND current_date() >= dtInicio
                      AND (isNull(dtFim) OR current_date() <= dtFim)";

        $row = parent::select($select,FALSE);
        return $row[0];		

    }

    ##########################################################################################

    function get_licenca($idServidor)


    # Função que informa licenca de uma matrícula
    #
    # Parâmetro: a matrícula a ser pesquisada

    {
            # Monta o select		
            $select = "SELECT tbtipolicenca.nome 
                         FROM tblicenca JOIN tbtipolicenca ON (tblicenca.idTpLicenca = tbtipolicenca.idTpLicenca)
                        WHERE idServidor = '$idServidor'
                          AND current_date() >= dtInicial 
                          AND current_date() <= ADDDATE(dtInicial,numDias-1)";

            $row = parent::select($select,FALSE);

            return $row[0];		

    }

    ##########################################################################################

    function get_licencaPeriodo($idLicenca)


    # Função que informa se a licença tem per�odo aquisitivo
    #
    # Parâmetro: id do tipo de licença

    {
            # Valida parametro
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


    # Função que informa se a licença necessita um processo administrativo
    #
    # Parâmetro: id do tipo de licença

    {
            # Valida parametro
            if(is_null($idLicenca)){
                return FALSE;
            }

            # Monta o select
            $select = 'SELECT processo
                         FROM tbtipolicenca
                        WHERE idTpLicenca = '.$idLicenca;

            $row = parent::select($select,FALSE);

            return $row[0];		

    }

    ##########################################################################################

    function get_licencaNumeroProcesso($idLicenca)


    # Função que informa o Número do processo de uma licença
    #
    # Parâmetro: id do tipo de licença

    {
            # Valida parametro
            if(is_null($idLicenca)){
                return FALSE;
            }

            # Monta o select
            $select = 'SELECT processo
                         FROM tblicenca
                        WHERE idLicenca = '.$idLicenca;

            $row = parent::select($select,FALSE);

            return $row[0];		

    }

    ##########################################################################################

    function get_licencaPublicacao($idLicenca)


    # Função que informa se a licença necessita de publicação no DOERJ
    #
    # Parâmetro: id do tipo de licença

    {
            # Valida parametro
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


    # Função que informa se esse tipo de licença necessita de perícia (licença m�dica)
    #
    # Parâmetro: id do tipo de licença

    {
            # Valida parametro
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


    # Função que informa o nome do tipo de licença
    #
    # Parâmetro: id do tipo de licença

    {
            # Valida parametro
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


    # Função que informa limitação por genero (sexo) do tipo de licença
    #
    # Parâmetro: id do tipo de licença

    {
            # Valida parametro
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


    # Função que informa a quantidade de dias fixos para esse tipo de licença
    #
    # Parâmetro: id do tipo de licença

    {
            # Valida parametro
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


    # Função que informa o tipo da licença de uma licença de um servidor
    #
    # Parâmetro: id da licença

    {
            # Valida parametro
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


    # Função que informa o nome de um tipo da licença
    #
    # Parâmetro: id do tipo da licença

    {
            # Valida parametro
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
     * Método get_situacao
     * informa a situa��o de um servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */

    function get_situacao($idServidor)
    {
            $select = 'SELECT tbsituacao.situacao
                         FROM tbservidor LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idsituacao)
                        WHERE idServidor = '.$idServidor;

            $situacao = parent::select($select,FALSE);

            return $situacao[0];
    }

    ###########################################################

    /**
     * Método get_idSituacao
     * informa a idsitua��o de um servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */

    function get_idSituacao($idServidor)
    {
            $select = 'SELECT idsituacao
                         FROM tbservidor LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idsituacao)
                        WHERE idServidor = '.$idServidor;

            $situacao = parent::select($select,FALSE);

            return $situacao[0];
    }

    ###########################################################

    /**
     * Método get_motivo
     * informa o motivo de saída de um servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */

    function get_motivo($idServidor)
    {
            $select = 'SELECT tbmotivo.motivo
                         FROM tbmotivo JOIN tbservidor ON (tbmotivo.idMotivo = tbservidor.motivo) 
                        WHERE idServidor = '.$idServidor;

            $motivo = parent::select($select,FALSE);

            return $motivo[0];
    }

    ###########################################################

    /**
     * Método get_idPessoaAtiva
     * informa se a pessoa tem alguma matrícula ativa
     * 
     * @param	integer $idPessoa   idPessoa do servidor
     */

    function get_idPessoaAtiva($idPessoa)
    {
            $select = 'SELECT idServidor
                         FROM tbservidor
                        WHERE situacao = 1 AND idPessoa = '.$idPessoa;

            $situacao = parent::select($select,FALSE);

            return $situacao[0];
    }

    ###########################################################

    /**
     * Método get_nome
     * fornece o nome de uma idServidor
     * 
     * @param	string $idServidor idServidor do servidor
     */

    function get_nome($idServidor)
    {
            if(is_numeric($idServidor)){
                $select = 'SELECT tbpessoa.nome
                             FROM tbservidor JOIN tbpessoa ON(tbservidor.idPessoa = tbpessoa.idPessoa)
                            WHERE idServidor = '.$idServidor;

                if($idServidor == 0){
                    $nome[0] = "";
                }else{ 
                    $nome = parent::select($select,FALSE);
                }
                return $nome[0];
            }else{
                return $idServidor;
            }
    }

    ###########################################################

    /**
     * Método get_sexo
     * informa o sexo de uma idServidor
     * 
     * @param	string $idServidor idServidor do servidor
     */

    function get_sexo($idServidor)
    {
        $select = 'SELECT tbpessoa.sexo
                     FROM tbpessoa JOIN tbservidor USING (idPessoa)
                    WHERE idServidor = '.$idServidor;
        
        $sexo = parent::select($select,FALSE);
        return $sexo[0];
    }

    ###########################################################

    /**
     * Método get_nomeidPessoa
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
     * Método get_cargoComissao
     * Informa o cargo em Comiss�o do Servidor (se tiver)
     * 
     * @param	string $idServidor  idServidor do servidor
     */

    function get_cargoComissao($idServidor)

    {
            # Pega o id do cargo em comissão (se houver)		 
            $select = 'SELECT idComissao
                         FROM tbcomissao
                        WHERE ((CURRENT_DATE BETWEEN dtNom AND dtExo)
                           OR (dtExo is NULL))
                        AND idServidor = '.$idServidor;

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
     * Método get_cargoComissaoPorId
     * Informa o id e o nome do cargo em Comiss�o do Servidor (se tiver)
     * usado na rotina de Cargo em comiss�o para preencher a combo
     * 
     * @param	string $idServidor  idServidor do servidor
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
     * Método get_salarioCargoComissao
     * Informa o sal�rio de um cargo em Comiss�o do Servidor (se tiver)
     * 
     * @param	string $idServidor  idServidor do servidor
     */

    function get_salarioCargoComissao($idServidor)

    {
            # Pega o id do cargo em comiss�o (se houver)		 
            $select = 'SELECT idComissao
                         FROM tbcomissao
                        WHERE dtExo is NULL AND idServidor = '.$idServidor;

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
     * Método get_trienioPercentual
     * informa o percentual atual do trienio de uma matrícula
     * 
     * @param	string $idServidor idServidor do servidor
     */

    function get_trienioPercentual($idServidor)
    {
            $select = 'SELECT percentual
                         FROM tbtrienio
                        WHERE idServidor = '.$idServidor.'
                             ORDER BY percentual desc';

            $row = parent::select($select,FALSE);

            return $row[0];

    }

    ###########################################################

    /**
     * Método get_trienioValor
     * informa o valor atual do trienio de uma matrícula
     * 
     * @param	string $idServidor idServidor do servidor
     */

    function get_trienioValor($idServidor)
    {
            $salario = $this->get_salarioBase($idServidor);
            $percentual = $this->get_trienioPercentual($idServidor);

            $valor = $salario * ($percentual/100);

            return $valor;

    }

    ###########################################################

    /**
     * Método get_trienioDataInicial
     * informa a data Inicial de um trienio de uma matrícula
     * 
     * @param	string $idServidor idServidor do servidor
     */

    function get_trienioDataInicial($idServidor)
    {
            $select = 'SELECT dtInicial
                         FROM tbtrienio
                        WHERE idServidor = '.$idServidor.'
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
     * @param	string $idServidor idServidor do servidor
     */

    function get_trienioDataProximoTrienio($idServidor)
    {
            $select = 'SELECT dtInicial
                         FROM tbtrienio
                        WHERE idServidor = '.$idServidor.'
                             ORDER BY percentual desc';

            $row = parent::select($select,FALSE);
            $dataTrienio = date_to_php($row[0]);

            $dataProximo = addAnos($dataTrienio, 3);  //Soma 3 anos ao último triênio recebido

            return $dataProximo;
    }

    ###########################################################

    /**
     * Método get_trienioPer�odoAquisitivo
     * informa a per�odo Aquisitivo de um trienio de uma matrícula
     * 
     * @param	string $idServidor idServidor do servidor
     */

    function get_trienioPeriodoAquisitivo($idServidor)
    {
            $select = 'SELECT dtInicioPeriodo,
                              dtFimPeriodo
                         FROM tbtrienio
                        WHERE idServidor = '.$idServidor.'
                             ORDER BY percentual desc';

            $row = parent::select($select,FALSE);

            return date_to_php($row[0]).' - '.date_to_php($row[1]);

    }

    ###########################################################

    /**
     * Método get_trienioNumProcesso
     * informa a n� do processo de um trienio de uma matrícula
     * 
     * @param	string $idServidor idServidor do servidor
     */

    function get_trienioNumProcesso($idServidor)
    {
            $select = 'SELECT numProcesso
                         FROM tbtrienio
                        WHERE idServidor = '.$idServidor.'
                             ORDER BY percentual desc';

            $row = parent::select($select,FALSE);

            return $row[0];

    }

    ###########################################################

    /**
     * Método get_trienioPublicacao
     * informa data e página da publicação no DOERJ do triênio vigente (último)
     * 
     * @param	string $idServidor idServidor do servidor
     */

    function get_trienioPublicacao($idServidor)
    {
            $select = 'SELECT dtPublicacao,
                              pgPublicacao
                         FROM tbtrienio
                        WHERE idServidor = '.$idServidor.'
                             ORDER BY percentual desc';

            $row = parent::select($select,FALSE);

            return date_to_php($row[0]).' - pág.'.$row[1];

    }

    ###########################################################

    /**
     * Método get_nomelotacao
     * Informa o nome da lota��o a partir do id
     *
     * @param	string $id  id da lota��o
     */

    public function get_nomeLotacao($idLotacao)

    {
            if (!is_numeric($idLotacao))
                return $idLotacao;
            else
            {
                $select = 'SELECT UADM,
                                  DIR,
                                  GER
                             FROM tblotacao
                            WHERE idLotacao = '.$idLotacao;

                $row = parent::select($select,FALSE);		
                return $row[0].'-'.$row[1].'-'.$row[2];
            }

    }

    ###########################################################

    /**
     * Método get_nomeCOmpletolotacao
     * Informa o nome da COmpleto lota��o a partir do id
     *
     * @param	string $id  id da lota��o
     */

    public function get_nomeCompletoLotacao($id)

    {
            if (!is_numeric($id))
                return $id;
            else
            {
                $select = 'SELECT nome,
                                  DIR,
                                  GER
                             FROM tblotacao
                            WHERE idLotacao = '.$id;

                $row = parent::select($select,FALSE);
                return $row[0].' - '.$row[1].'/'.$row[2];
            }

    }

    ###########################################################

    /**
     * Método get_servidoresCargo
     * 
     * Exibe o n�mero de servidores ativos em um determinado cargo
     */

    public function get_servidoresCargo($id)
    {
        $select = 'SELECT idServidor                             
                     FROM tbservidor
                    WHERE situacao = 1 AND 
                          idCargo = '.$id;

        $numero = parent::count($select);
        return $numero;
    }

    ###########################################################

    /**
     * Método get_servidoresTipoCargo
     * 
     * Exibe o número de servidores ativos em um determinado tipo de cargo
     */

    public function get_servidoresTipoCargo($id)
    {
        $select = 'SELECT idServidor                             
                     FROM tbservidor JOIN tbcargo USING (idCargo)
                    WHERE situacao = 1 AND 
                          tbcargo.idTipoCargo = '.$id;

        $numero = parent::count($select);
        return $numero;
    }

    ###########################################################

    /**
     * Método get_servidoresCargoComissao
     * 
     * Exibe o n�mero de servidores ativos em um determinado cargo em comissao
     */

    public function get_servidoresCargoComissao($id)
    {
        $select = 'SELECT tbservidor.idServidor                             
                     FROM tbservidor JOIN tbcomissao ON (tbservidor.idServidor = tbcomissao.idServidor)
                    WHERE situacao = 1
                      AND dtExo is NULL
                      AND tbcomissao.idTipoComissao = '.$id;

        $numero = parent::count($select);
        return $numero;
    }

    ###########################################################

    /**
     * Método get_cargoComissaoVagas
     * 
     * Exibe o símbolo de um determinado cargo em comissao
     */

    public function get_cargoComissaoSimbolo($id)
    {
        $select = 'SELECT simbolo                             
                     FROM tbtipocomissao 
                    WHERE idTipoComissao = '.$id;

        $row = parent::select($select,FALSE);		
        return $row[0];
    }

    ###########################################################

    /**
     * Método get_cargoComissaoVagas
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
     * Método get_cargoComissaoVagas
     * 
     * Exibe o número de vagas não ocupadas em um determinado cargo em comissao
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
     * Método get_servidoresConcurso
     * 
     * Exibe o n�mero de servidores ativos em um determinado concurso
     */

    public function get_servidoresConcurso($id)
    {
        $select = 'SELECT idServidor                             
                     FROM tbservidor
                    WHERE situacao = 1 AND 
                          idConcurso = '.$id;

        $numero = parent::count($select);
        return $numero;
    }

    ###########################################################

    /**
     * Método get_servidoresAtivosPerfil
     * 
     * Exibe o número de servidores ativos 
     */

    public function get_servidoresAtivosPerfil($id)
    {
        $select = 'SELECT idServidor                             
                     FROM tbservidor
                    WHERE situacao = 1 AND 
                          idPerfil = '.$id;

        $numero = parent::count($select);
        return $numero;
    }

    ###########################################################

    /**
     * Método get_servidoresAtivosPerfil
     * 
     * Exibe o número de servidores inativos 
     */

    public function get_servidoresInativosPerfil($id)
    {
        $select = 'SELECT idServidor                             
                     FROM tbservidor
                    WHERE situacao <> 1 AND 
                          idPerfil = '.$id;

        $numero = parent::count($select);
        return $numero;
    }

    ###########################################################

    /**
     * Método get_servidoressituacaouacao
     * 
     * Exibe o n�mero de servidores ativos em um determinado concurso
     */

    public function get_servidoresSituacao($id)
    {
        $select = 'SELECT idServidor                             
                     FROM tbservidor
                    WHERE situacao = '.$id;

        $numero = parent::count($select);
        return $numero;
    }

    ###########################################################

    /**
     * Método get_perfilLicenca
     * informa se o perfil é permitido tirar licença
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
     * Método get_perfilProgressao
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
     * Método get_perfilTrienio
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
     * Método get_perfilComissao
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
     * Método get_perfilMatricula
     * informa a matrícula inicial e final permitida a um perfil
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
     * Método get_perfilGratificacao
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
     * Método get_perfilQuantidade
     * informa se o n�mero de servidores ativos nesse perfil
     * 
     * @param   integer $id id do Perfil
     */

    public function get_perfilQuantidade($id)
    {
            $select = 'SELECT idServidor
                         FROM tbservidor
                        WHERE situacao = 1
                          AND idPerfil = '.$id;

            $count = parent::count($select);

            return $count;

    }

    ###########################################################

    /**
     * Método get_perfilFerias
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
     * Método get_nivelCargo
     * Informa o n�vel de escolaridade do cargo de uma matrícula
     * 
     * @param   string $idServidor do servidor
     */

    public function get_nivelCargo($idServidor)
    {
        $select = 'SELECT tbtipocargo.nivel
                     FROM tbservidor JOIN tbcargo USING (idCargo)
                                     JOIN tbtipocargo USING (idTipoCargo)
                    WHERE idServidor = '.$idServidor;

        $row = parent::select($select,FALSE);

        return $row[0];
    }

    ###########################################################

    public function get_telefones($idServidor)

    /**	
     * Informa os telefones de um servidor
     * 
     * @param   string $idServidor do servidor
     */


    {
        $select = 'SELECT tbcontatos.numero
                     FROM tbcontatos 
                LEFT JOIN tbpessoa on tbcontatos.idPessoa = tbpessoa.idPessoa
                LEFT JOIN tbservidor on tbpessoa.idPessoa = tbservidor.idPessoa
                    WHERE tbcontatos.tipo <> "E-mail" 
                      AND tbservidor.idServidor = '.$idServidor;		

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

    public function mudaStatusFeriasSolicitadaFruida(){      

    /**	
     * Função acerta o status das férias de acordo com a data atual. 
     * Muda para fruídas as férias que foram solicitadas cuja data inicial já passou e
     * Muda para solicitadas as férias que foram fruídas cuja data inicial ainda não passou
     */
        
        # primeira alteração
        $sql = 'UPDATE tbferias SET status = "fruída"
                 WHERE status = "solicitada"
                   AND dtInicial < current_date()';
        parent::update($sql);
        
        # segunda alteração
        $sql = 'UPDATE tbferias SET status = "solicitada"
                 WHERE status = "fruída"
                   AND dtInicial > current_date()';
        parent::update($sql);
    }

    ###########################################################

    function get_idDbv($idServidor)

    /**
     *
     * informa o id da tabela tddbv
     * 
     * @param	string $idServidor idServidor do servidor
     */


    {
            $select = 'SELECT idDbv
                         FROM tbdbv
                        WHERE idServidor = '.$idServidor;

            $row = parent::select($select,FALSE);
            $count = parent::count($select);

            if($count == 0)
                return NULL;
            else
                return $row[0];
    }

    ###########################################################

    function get_dbvAcumulacao($idServidor)

    /**
     *
     * informa se o servidor tem ou não cargo acumulado
     * 
     * @param	string $idServidor idServidor do servidor
     */


    {
        $select = 'SELECT acumulacao
                     FROM tbdbv
                    WHERE idServidor = '.$idServidor;

        $row = parent::select($select,FALSE);
        $count = parent::count($select);

        if ($count == 0) {
            return NULL;
        } else {
            return $row[0];
        }
    }

    ###########################################################

    function get_dbvAnoBase($idServidor)

    /**
     *
     * informa o ano base da dbv
     * 
     * @param	string $idServidor idServidor do servidor
     */


    {
            $select = 'SELECT anoBase
                         FROM tbdbv
                        WHERE idServidor = '.$idServidor;

            $row = parent::select($select,FALSE);
            $count = parent::count($select);

            if($count == 0)
                return NULL;
            else
                return $row[0];
    }

    ###########################################################

    function get_folgasConcedidas($idServidor)

    /**
     * informa a quantidade de dias de folga que o servidor tem direito
     * 
     * @param	string $idServidor idServidor do servidor
     */


    {
            $select = 'SELECT IFNULL(sum(folgas),0)
                         FROM tbtrabalhotre
                        WHERE idServidor = '.$idServidor;

            $row = parent::select($select,FALSE);
            $count = parent::count($select);

            if($count == 0)
                return '0';
            else
                return $row[0];
    }

    ###########################################################

    function get_folgasFruidas($idServidor)

    /**
     * informa a quantidade de folga que o servidor fruiu (tirou)
     * 
     * @param	string $idServidor idServidor do servidor
     */


    {
            $select = 'SELECT IFNULL(sum(dias), 0)
                         FROM tbfolga
                        WHERE idServidor = '.$idServidor;

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
     * informa se a idServidor informada existe no cadastro
     * 
     * @param	string $matricula A matrícula do servidor
     */


    {
        $select = 'SELECT idServidor
                     FROM tbservidor
                    WHERE matricula = '.$matricula;		

        $count = parent::count($select);

        if($count == 0){
            return FALSE;
        }else{
            return TRUE;
        }
    }

    ###########################################################

    function get_novaMatricula($perfil)

    /**
     * informa uma matrícula nova (gera nova matrícula)
     * 
     * @param	string $perfil perfil para saber a faixa da matrícula
     */

    {
        # pega a faixa da matrícula para esse perfil
        $faixa = $this->get_perfilMatricula($perfil);
        if(is_null($faixa[0])){
            return "-";
        }else{

            # pega a última matrícula utilizada nessa faixa
            $select = 'SELECT matricula
                         FROM tbservidor
                        WHERE matricula >= '.$faixa[0].'
                          AND matricula < '.$faixa[1].'
                     ORDER BY matricula desc';		

            $row = parent::select($select,FALSE);
            $count = parent::count($select);            

            # se não tiver nenhum idServidor cadastrada nessa faixa pega-se a matrícula inicial da faixa                
            if($count == 0){
                # Diferencia se o select voltou vazio por não ter ninguém na faixa
                # ou se por não ter matrícula vaga na faixa.
                if($pessoal->get_existeMatricula($faixa[1])){
                    # Já ocupado o último valor dessa faixa
                    alert("Não há mais matrículas vagas para esse perfil./nAumente o número de matrículas no cadastro de perfil.");
                    back(1);
                }else{
                    # Faixa vazia então pega-se o primeiro valor
                    $novaMatricula = $faixa[0];
                }
            }else{
                # pega a última matrícula
                $ultimaMatricula = $row[0];
                $novaMatricula = $ultimaMatricula +1;
            }

            return $novaMatricula;
        }
    }

    ###########################################################

    /**
     * Método get_nivelCargoCargo
     * Informa o n�vel de escolaridade do cargo de um idCargo
     * 
     * @param   string $idCargo
     */

    public function get_nivelCargoCargo($idCargo)
    {
        $select = 'SELECT idTipoCargo
                     FROM tbcargo
                    WHERE idCargo = '.$idCargo;

        $row = parent::select($select,FALSE);

        return $row[0];
    }

    ###########################################################

    /**
     * Método get_planoCargosAtual
     * Informa o id do Plano de CArgos Atual (com a data de publicação mais recente)
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
     * Método get_classeInicial
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
     * Método get_Pis
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
     * Método get_perfilNome
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
     * Método get_nomeCargo
     * Informa o nome do cargo a partir do id
     *
     * @param	string $id  id do cargo
     */

    public function get_nomeCargo($idCargo)

    {
            if (!is_numeric($idCargo))
                return $idCargo;
            else
            {
                $select = 'SELECT nome
                             FROM tbcargo
                            WHERE idCargo = '.$idCargo;

                $row = parent::select($select,FALSE);
                return $row[0];
            }

    }

    ##########################################################

    /**
     * Método get_nomeCargoComissao
     * Informa o nome do cargo em comissao a partir do id
     *
     * @param	string $id  id do cargo
     */

    public function get_nomeCargoComissao($id)

    {
        if (!is_numeric($id))
            return $id;
        else
        {
           $select ='SELECT tbtipocomissao.descricao 
                            FROM tbtipocomissao 
                           WHERE idTipoComissao = '.$id;

            $row = parent::select($select,FALSE);
            return $row[0];
        }
    }

    ###########################################################

    /**
     * Método get_nomeArea
     * Informa o nome da área a partir do id
     *
     * @param	string $id  id do cargo
     */

    public function get_nomeArea($id)

    {
        if (!is_numeric($id))
            return $id;
        else
        {
            $select = 'SELECT area
                         FROM tbarea
                        WHERE idArea = '.$id;

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

    function get_numServidoresAtivos($idLotacao = NULL){

    /**
     * informa o número de Servidores Ativos
     * 
     * @param integer $idPessoa do servidor
     */
        
        $select = 'SELECT idServidor
                     FROM tbservidor
                     JOIN tbhistlot USING (idServidor)
                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                    WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                      AND situacao = 1';	

        # Lotação
        if((!is_null($idLotacao)) AND ($idLotacao <> "*")){
            $select .= ' AND (tblotacao.idlotacao = "'.$idLotacao.'")';
        }

        $count = parent::count($select);
        return $count;
    }

    ###########################################################

    /**
     * Método get_ultimoAcesso
     * informa a data do �ltimo acesso a �rea do servidor de uma matrícula
     * 
     * @param	string $idServidor idServidor do servidor
     */

    function get_ultimoAcesso($idServidor)
    {
            $select = 'SELECT date(ult_acesso)
                         FROM tbservidor
                        WHERE idServidor = '.$idServidor;

            # verifica se a idServidor foi informada
            if(is_null($idServidor))
                $data[0] = "1900-01-01";
            else
            {
                $data = parent::select($select,FALSE);
            }

            return $data[0];
    }

    ##########################################################################################

    function aniversariante($idServidor)


    # Função que informa se a idServidor est� fazendo anivers�rio na data atual
    #
    # Parâmetro: a matrícula a ser pesquisada

    {
            # Pega a data de nascimento		
            $select = "SELECT date_format(tbpessoa.dtNasc,'%d/%m')
                         FROM tbpessoa JOIN tbservidor ON(tbservidor.idPessoa = tbpessoa.idPessoa)
                        WHERE idServidor = '$idServidor'";

            $row = parent::select($select,FALSE);

            # Divide a data 
            $data = explode('/',$row[0]); 
            $d = $data[0];   // dia
            $m = $data[1];   // mês

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
     * Método get_parentesco
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
    
    function get_licencaDados($idLicenca)


    # Função que informa v�rios dados de uma licença
    #
    # Função usada na rotina tempor�rio que transforma uma licença em publicação de licença premio
    # 
    # Parâmetro: id da licença

    {
            # Valida parametro
            if(is_null($idLicenca))
                return FALSE;

            # Monta o select		
            $select = 'SELECT processo,
                              dtInicioPeriodo,
                              dtFimPeriodo,
                              dtPublicacao,
                              pgPublicacao,
                              obs,
                              idServidor                              
                         FROM tblicenca
                        WHERE idLicenca = '.$idLicenca;

            $row = parent::select($select,FALSE);
            return $row;		

    }

    ###########################################################

    /**
     * Método get_cpf
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
     * Método get_identidade
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
     * ou NULLo se não tiver feriado nessa data
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

    function emFolgaTre($idServidor)

    # Função que informa se a idServidor est� folgando (TRE) na data atual
    #
    # Parâmetro: a matrícula a ser pesquisada

    {
        # Monta o select
        $select = "SELECT idFolga
                     FROM tbfolga
                    WHERE idServidor = '$idServidor'
                      AND current_date() >= data 
                      AND current_date() <= ADDDATE(data,dias-1)";

        $row = parent::select($select,FALSE);
        
        if(is_null($row[0]))
            return 0;
        else 
            return 1;
    }

    ##########################################################################################

    function emAfastamentoTre($idServidor)

    # Função que informa se a idServidor está afastada para o (TRE) na data atual
    #
    # Parâmetro: a matrícula a ser pesquisada

    {
        # Monta o select
        $select = "SELECT idTrabalhoTre
                     FROM tbtrabalhotre
                    WHERE idServidor = '$idServidor'
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
	 * Método get_idFuncional
	 * Informa a idFuncional de um servidor
	 * 
	 * @param	string $idServidor  idServidor do servidor
	 */

	public function get_idFuncional($idServidor){
            # Pega o cargo do servidor
            $select = 'SELECT idFuncional
                         FROM tbservidor
                        WHERE idServidor = '.$idServidor;

            $row = parent::select($select,FALSE);
            
            return $row[0];
	}
		
    		
###########################################################
	
    /**
     * Método get_tipoSenha
     * Informa o tipo da senha (padrão/bloqueada/Ok) 
     * 
     * @param	string $idServidor	idServidor do servidor
     */

    function get_tipoSenha($idServidor)
    { 

        $select = "SELECT senha_intra		  
                     FROM tbservidor
                    WHERE idServidor = ".$idServidor;
        
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

	
	/**
	 * Método get_idServidor
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
	 * Método get_idServidorFerias
	 * Informa o id servidor de um pedido de férias
	 * 
	 * @param	string $idFerias  matricula do servidor
	 */

	public function get_idServidorFerias($idFerias)
	
	{
            # Pega o cargo do servidor
            $select = 'SELECT idServidor
                         FROM tbferias
                        WHERE idFerias = '.$idFerias;

            $row = parent::select($select,FALSE);
            
            return $row[0];
			
	}
		
    ###########################################################
	
	function get_numCargoComissaoAtivo()
	
	/**
	 * informa o número de Lotações ativas
	 */


	{
            $select = 'SELECT idTipoComissao
                         FROM tbtipocomissao
                        WHERE ativo';		

            $count = parent::count($select);

            return $count;
	}
	
	###########################################################
        
	
	function get_numLotacaoAtiva()
	
	/**
	 * informa o número de Lotações ativas
	 */


	{
            $select = 'SELECT idLotacao
                         FROM tblotacao
                        WHERE ativo';		

            $count = parent::count($select);

            return $count;
	}
	
	###########################################################
        
	/**
	 * Método get_TipoCargoVagas
	 * 
	 * Exibe o n�mero de vagas em um determinado cargo em comissao
	 */
	
	public function get_TipoCargoVagas($id)
	{
            $select = 'SELECT vagas                             
                         FROM tbtipocargo 
                        WHERE idTipoCargo = '.$id;
           
            $row = parent::select($select,FALSE);		
            return $row[0];
	}

	###########################################################
	
	/**
	 * Método get_tipoCargoVagasDisponiveis
	 * 
	 * Exibe o número de vagas não ocupadas em um determinado cargo em comissao
	 */
	
	public function get_tipoCargoVagasDisponiveis($id)
	{
            $vagas = $this->get_TipoCargoVagas($id);
            $ocupadas = $this->get_servidoresTipoCargo($id);
            $disponiveis = $vagas - $ocupadas;
           
            return $disponiveis;
	}

	###########################################################
	
	/**
	 * Método get_servidoresArea
	 * 
	 * Exibe o número de servidores ativos em uma determinada area
	 */
	
	public function get_servidoresArea($id)
	{
            $select = 'SELECT idServidor                             
                         FROM tbservidor LEFT JOIN tbcargo USING (idCargo)
                        WHERE situacao = 1 AND 
                              tbcargo.idArea = '.$id;
           
            $numero = parent::count($select);
            return $numero;
	}

	###########################################################
	
	/**
	 * Método get_nomeSituacao
	 * 
	 * Informa o nome de um idsituacao
	 */
	
	public function get_nomeSituacao($idsituacao)
	{
            $select = 'SELECT situacao                            
                         FROM tbsituacao
                        WHERE idsituacao = '.$idsituacao;
           
             $row = parent::select($select,FALSE);
             return $row[0];
	}

	###########################################################
	
	/**
	 * Método get_nomePerfil
	 * 
	 * Informa o nome de um idperfil
	 */
	
	public function get_nomePerfil($idperfil)
	{
            $select = 'SELECT nome                            
                         FROM tbperfil
                        WHERE idperfil = '.$idperfil;
           
             $row = parent::select($select,FALSE);
             return $row[0];
	}

	###########################################################

	/**
	 * Método get_nomeCompletoCargo
	 * Informa o nome do cargo a partir do id
	 *
	 * @param	string $id  id do cargo
	 */

	public function get_nomeCompletoCargo($id)

	{
		if (!is_numeric($id))
                    return $id;
                else
                {
                    $select = 'SELECT tbtipocargo.cargo,
                                      tbarea.area,
                                      tbcargo.nome
                                 FROM tbcargo LEFT JOIN tbtipocargo USING (idTipoCargo)
                                              LEFT JOIN tbarea USING (idarea)
                                WHERE idCargo = '.$id;

                    $row = parent::select($select,FALSE);
                    return $row[0]." - ".$row[1]." - ".$row[2];
                }

	}

	###########################################################
	
	/**
	 * Método get_numIdservidor
	 * fornece o número de idservidores de um idpessoa
	 * 
	 * @param	string $idPessoa idPessoa do servidor
	 */

	public function get_numIdservidor($idPessoa)
	{
		$select = 'SELECT idservidor
                             FROM tbservidor
                            WHERE idpessoa = '.$idPessoa;
		
		$numIdservidor = parent::count($select);
				
		return $numIdservidor;
	}
	
	#####################################################################################
	
	/**
	 * Método get_nomeConcurso
	 * 
	 * Informa o nome de um idconcurso	 */
	
	public function get_nomeConcurso($idconcurso)
	{
            $select = 'SELECT CONCAT(anobase," - ",regime)                          
                         FROM tbconcurso
                        WHERE idconcurso = '.$idconcurso;
           
             $row = parent::select($select,FALSE);
             return $row[0];
	}

	#####################################################################################
	
	/**
	 * Método get_feriasResumo
	 * 
	 * Fornece um array com a lista de totais de dias fruidos/solicitados por ano de exercicio
         */
	
	public function get_feriasResumo($idservidor){
            $select = 'SELECT anoexercicio, SUM(numDias)                          
                         FROM tbferias
                        WHERE idservidor = '.$idservidor.'
                          AND (status = "fruída" OR status = "solicitada" OR status = "confirmada")
                     GROUP BY anoexercicio
                     ORDER BY anoexercicio asc';
           
             $row = parent::select($select);
             return $row;
	}

	#####################################################################################
	
	/**
	 * Método get_exercicioDisponivel
	 * 
	 * Informa o ano exercício das férias disponivel para fruir
         */
	
	public function get_feriasExercicioDisponivel($idservidor){
            
            # Pega as férias cadastradas no sistema
            $lista = $this->get_feriasResumo($idservidor);
            
            # Pega o ano de admissão do servidor
            $anoAdmissao = $this->get_anoAdmissao($idservidor);
            
            # Pega o ano atual
            $anoAtual = date("Y");
            
            # Definr a variável
            $retorno = NULL;
            $ultimo = NULL;
            
            # Se não tiver férias cadastradas o ano disponível é o posterior ao da admissão
            if(count($lista) == 0){
                $retorno = $anoAdmissao + 1;
            }else{
                # Se houver verifica se alguma das férias tem menos de 30 dias 
                foreach ($lista as $value){
                    if($value[1] < 30){
                        $retorno = $value[0];
                    }
                    $ultimo = $value[0];
                }
            }
            
            # 
            if(is_null($retorno)){
                $retorno = $ultimo+1;
            }
            return $retorno;
	}

	#####################################################################################
	
	/**
	 * Método get_emailPrincipalServidor(idServidor)
	 * 
	 * Informa se o servidor tem email principal e qual seria
         */
	
	public function get_emailPrincipalServidor($idServidor){
            $select = 'SELECT numero
                         FROM tbcontatos LEFT JOIN tbservidor USING (idPessoa)
                        WHERE tipo = "E-mail Principal" AND idservidor = '.$idServidor;
           
             $row = parent::select($select,FALSE);
             return $row[0];
	}

	#####################################################################################
	
	/**
	 * Método get_feriasSomaDias
	 * 
	 * Informa os dias de férias fruídas ou solicitadas de um servidor em um ano exercicio,
         */
	
	public function get_feriasSomaDias($anoExercicio,$idservidor,$id=NULL){
            $select = 'SELECT anoexercicio, SUM(numDias)                          
                         FROM tbferias
                        WHERE idservidor = '.$idservidor.'
                          AND anoExercicio = '.$anoExercicio;
            
            # Retira o id ferias do calculo caso seja edição desse mesmo registro
            # para não contá-lo 2 vezes
            if(!vazio($id)){
                $select .= ' AND idFerias <> '.$id;
            }
            
            $select .= ' GROUP BY anoexercicio
                         ORDER BY anoexercicio asc';
           
             $row = parent::select($select,FALSE);
             return $row[1];
	}
        
        #####################################################################################
	
	/**
	 * Método get_feriasQuantidadesPeriodos
	 * 
	 * Informa os períodos solicitados por um servidor em um anoexercicio
         */
	
	public function get_feriasPeriodo($idFerias){
            # Pega os dados dessas ferias
            $select1 = "SELECT idServidor,
                              dtInicial,
                              anoExercicio,
                              numDias,
                              status
                         FROM tbferias
                         WHERE idFerias = $idFerias";
            
            $ferias = parent::select($select1,false);
            
            # Preenche as variáveis
            $idServidor = $ferias[0];
            $dtInicial = $ferias[1];
            $anoExercicio = $ferias[2];
            $numDias = $ferias[3];
            $status = $ferias[4];
            $periodo = NULL;
            
            # Verifica as férias desse servidor nesse periodo
            $select2 = "SELECT idFerias
                          FROM tbferias
                         WHERE idServidor = $idServidor
                           AND anoExercicio = $anoExercicio
                      ORDER BY dtInicial asc";       

            $listaFerias = parent::select($select2);

            # Percorre as féras desse servidor no exercicio informado
            # para saber em que lugar na ordem ela se encontra
            $ordem = 1;            
            foreach ($listaFerias as $value){
                if($value[0] == $idFerias){
                    $periodo = $ordem."º";
                }
                $ordem ++;
                
                # Se for único
                if($numDias == 30){
                    $periodo = "Único";
                }
            }
            
            return $periodo;
	}
        
        ###########################################################
	
	/**
	 * Método get_dataNascimento
	 * informa a data de nascimento de um idServidor
	 * 
	 * @param	string $idServidor idServidor do servidor
	 */

	function get_dataNascimento($idServidor)
	{
            $select = 'SELECT tbpessoa.dtNasc
                         FROM tbpessoa JOIN tbservidor USING(idPessoa)
                        WHERE tbservidor.idServidor = '.$idServidor;

            if($idServidor == 0){
                alert("$idServidor inválido");
            }else{ 
                $nome = parent::select($select,FALSE);
                return date_to_php($nome[0]);
            }
	}
	
	###########################################################

	
	/**
	 * Método get_idServidoridFuncional
	 * Informa a idServidor de um idFuncional
	 * 
	 * @param	string $idFuncional  idFuncional do servidor
	 */

	public function get_idServidoridFuncional($idFuncional){
            # Pega o cargo do servidor
            $select = 'SELECT idServidor
                         FROM tbservidor
                        WHERE idFuncional = '.$idFuncional;

            $row = parent::select($select,FALSE);
            
            return $row[0];
			
	}
        
        ###########################################################
	
	/**
	 * Método get_dataAposentadoria
	 * Informa a data em que o servidor passa a ter direito a solicitar aposentadoria
	 * 
	 * @param string $idServidor idServidor do servidor
	 */

	public function get_dataAposentadoria($idServidor){
            # Pega o sexo do servidor
            $sexo = $this->get_sexo($idServidor);
            
            # Define a idade que dá direito para cada gênero
            switch ($sexo){
                case "Masculino" :
                    $idade = 60;
                    break;
                case "Feminino" :
                    $idade = 55;
                    break;
            }
            
            # Pega a data de nascimento (vem dd/mm/AAAA)
            $dtNasc = $this->get_dataNascimento($idServidor);
            $partes = explode("/",$dtNasc);
            
            # Soma
            $novoAno  = $partes[2]+$idade;
            
            # Calcula a data
            $novaData = $partes[0]."/".$partes[1]."/".$novoAno;            
            return $novaData;			
	}
        
        ###########################################################
	
	/**
	 * Método get_dataCompulsoria
	 * Informa a data em que o servidor é obrigado a se aposentar
	 * 
	 * @param string $idServidor idServidor do servidor
	 */

	public function get_dataCompulsoria($idServidor){
            
            # Idade obrigatória
            $idade = 75;
            
            # Pega a data de nascimento (vem dd/mm/AAAA)
            $dtNasc = $this->get_dataNascimento($idServidor);
            $partes = explode("/",$dtNasc);
            
            # Soma
            $novoAno  = $partes[2]+$idade;
            
            # Calcula a data
            $novaData = $partes[0]."/".$partes[1]."/".$novoAno;            
            return $novaData;			
	}
        
        ###########################################################
	
	/**
	 * Método get_idade
	 * Informa a idade do servidor
	 * 
	 * @param string $idServidor idServidor do servidor
	 */

        public function get_idade($idServidor){
            
            # Pega a data de nascimento do servidor
            $dataNascimento = $this->get_dataNascimento($idServidor);
            
            
            # Separa em dia, mês e ano
            list($dia, $mes, $ano) = explode('/', $dataNascimento);

            # Descobre que dia é hoje e retorna a unix timestamp
            $hoje = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            
            # Descobre a unix timestamp da data de nascimento
            $nascimento = mktime( 0, 0, 0, $mes, $dia, $ano);

            # Depois apenas fazemos o cálculo já citado :)
            $idade = floor((((($hoje - $nascimento) / 60) / 60) / 24) / 365.25);
            
            return $idade;
        }

        ###########################################################
	
	/**
	 * Método temLotacaoNestaData
	 * Verifica se servidor já tem lotação na data informada. Evita problema de duplicidade de servidor quando tem 2 ou mais lotações começando na mesma data.
	 * 
	 * @param data    $data       A data do início da lotação a ser verificadaidServidor do servidor
         * @param integer $idServidor O idServidor do servidor em questão
         * @param integer $idHistLot  O id da tabela tbhistlot para certificar que não está comparando o mesno registro.
	 */

        public function temLotacaoNestaData($data, $idServidor, $idHistLot = NULL){
            
            $select = 'SELECT data
                         FROM tbhistlot
                         WHERE idServidor = '.$idServidor.'
                           AND data = "'.$data.'"';
            
            if(!is_null($idHistLot)){
                $select .= ' AND idHistLot <> '.$idHistLot;
            }

            $result = parent::count($select);
            
            if($result > 0){
                return TRUE;
            }else{
                return FALSE;
            }
        }

        ###########################################################
	
	/**
	 * Método podeNovoServidor
	 * Verifica se é permitido incluir novos servidores nesse perfil.
	 *
         * @param integer $idPerfil O idPerfil do servidor a ser incluído
	 */

        public function podeNovoServidor($idPerfil){
            
            $select = 'SELECT novoServidor
                         FROM tbperfil
                         WHERE idPerfil = '.$idPerfil;
            
            $result = parent::select($select,FALSE);
            
            return $result[0];
        }

        ##########################################################################################

        function get_licencaLei($idTipoLicenca)


        # Função que informa o nome do tipo de licença
        #
        # Parâmetro: id do tipo de licença

        {
                # Valida parametro
                if(is_null($idTipoLicenca))
                    return FALSE;

                # Monta o select		
                $select = 'SELECT lei
                             FROM tbtipolicenca
                            WHERE idTpLicenca = '.$idTipoLicenca;

                $row = parent::select($select,FALSE);

                return $row[0];		

        }

    ##########################################################################################

        function get_contatos($idServidor){

        # Função que retorna string com todos os contatos de um servidor separados por virgula
        #
        # Parâmetro: id do servidor
        
                # Valida parametro
                if(is_null($idServidor)){
                    return FALSE;
                }
                
                # Pega o idPessoa desse idServidor
                $idPessoa = $this->get_idPessoa($idServidor);
                
                # Monta o select		
                $select = 'SELECT tipo,
                                  numero
                             FROM tbcontatos
                            WHERE idPessoa = '.$idPessoa;

                $row = parent::select($select);
                $numero = parent::count($select);
                $contador = 1;
                $return = NULL;
                
                if($numero>0){
                    # Percorre o array e preenche o $return
                    foreach ($row as $valor) {
                        $return .= $valor[0].": ".strtolower($valor[1]);

                        if($contador < $numero){
                            $return .= ",<br/>";
                            $contador++;
                        }
                    }
                }

                return $return;		

        }

    ##########################################################################################

        function get_email($idServidor){

        # Função que retorna string com todos os emails de um servidor separados por virgula
        #
        # Parâmetro: id do servidor
        
            # Valida parametro
            if(is_null($idServidor)){
                return FALSE;
            }

            # Pega o idPessoa desse idServidor
            $idPessoa = $this->get_idPessoa($idServidor);

            # Monta o select		
            $select = 'SELECT numero
                         FROM tbcontatos
                        WHERE (tipo = "E-mail" OR tipo = "E-mail Principal")
                          AND idPessoa = '.$idPessoa;

            $row = parent::select($select);
            $numero = parent::count($select);
            $contador = 1;
            $return = NULL;

            if($numero>0){
                # Percorre o array e preenche o $return
                foreach ($row as $valor) {
                    $return .= strtolower($valor[0]);

                    if($contador < $numero){
                        $return .= ",<br/>";
                        $contador++;
                    }
                }
            }

            return $return;		

        }

    ##########################################################################################

        function get_idCedido($idServidor)

        /**
        * Função que informa o idCedido existe um registro para esse servidor no cadastro de cedidos
        */

        {
            # Valida parametro
            if(is_null($idServidor))
                return FALSE;

            # Monta o select		
            $select = 'SELECT idCedido
                         FROM tbcedido
                        WHERE idServidor = '.$idServidor;

            $row = parent::select($select,FALSE);

            return $row[0];
        }

    ##########################################################################################

	
	
}
