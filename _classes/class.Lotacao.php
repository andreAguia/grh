<?php

class Lotacao {

    /**
     * Classe que abriga as várias rotina de Lotação
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ###########################################################

    public function getLotacao($idLotacao = null) {
        /**
         * Retorna o nome da lotação
         * 
         * @syntax $this->get_dados($idRpa);
         */
        if (empty($idLotacao)) {
            return null;
        } else {
            $pessoal = new Pessoal();

            # Pega os dados
            $select = "SELECT UADM,
                              DIR,
                              GER
                       FROM tblotacao
                      WHERE idLotacao = {$idLotacao}";

            $row = $pessoal->select($select, false);
            if ($row["UADM"] <> "UENF") {
                return "{$row["UADM"]} - {$row["DIR"]} - {$row["GER"]}";
            } else {
                return "{$row["DIR"]} - {$row["GER"]}";
            }
        }
    }

    ###########################################################

    public function getRamais($idLotacao = null) {
        /**
         * Retorna o nome da lotação
         * 
         * @syntax $this->get_dados($idRpa);
         */
        if (empty($idLotacao)) {
            return null;
        } else {
            $pessoal = new Pessoal();

            # Pega os dados
            $select = "SELECT ramais
                         FROM tblotacao
                        WHERE idLotacao = {$idLotacao}";

            $row = $pessoal->select($select, false);
            return $row["ramais"];
        }
    }

    ###########################################################

    public function getNomeLotacaoAnterior($idHistLot = null) {
        /**
         * Retorna a lotação anterior deste servidor a partir de uma mudança de lotação no histórico 
         * 
         * @syntax $this->getLotacaoAnterior($idRpa);
         */
        if (empty($idHistLot)) {
            return null;
        } else {
            $pessoal = new Pessoal();
            echo $this->getLotacao($this->getLotacaoAnterior($idHistLot));
        }
    }

    ###########################################################

    public function getLotacaoAnterior($idHistLot = null) {
        /**
         * Retorna a lotação anterior deste servidor a partir de uma mudança de lotação no histórico 
         * 
         * @syntax $this->getLotacaoAnterior($idRpa);
         */
        if (empty($idHistLot)) {
            return null;
        } else {
            $pessoal = new Pessoal();

            # Pega o servidor desta alteração de lotação
            $select1 = "SELECT idServidor
                          FROM tbhistlot
                         WHERE idHistLot = {$idHistLot}";

            $row1 = $pessoal->select($select1, false);
            $idServidor = $row1["idServidor"];

            # Agora pega a lotação anterior a esta mudança
            $select2 = "SELECT idHistLot,
                               lotacao 
                          FROM tbhistlot 
                         WHERE idServidor = {$idServidor}
                      ORDER BY data";

            $row2 = $pessoal->select($select2);

            $lotacao = null;
            foreach ($row2 as $item) {

                if ($item["idHistLot"] == $idHistLot) {
                    return $lotacao;
                } else {
                    $lotacao = $item["lotacao"];
                }
            }
        }
    }

    ###########################################################

    public function getLotacaoPosterior($idHistLot = null) {
        /**
         * Retorna a lotação Posterior deste servidor a partir de uma mudança de lotação no histórico 
         * 
         * @syntax $this->getLotacaoAnterior($idRpa);
         */
        if (empty($idHistLot)) {
            return null;
        } else {
            $pessoal = new Pessoal();

            # Pega o servidor desta alteração de lotação
            $select1 = "SELECT idServidor
                         FROM tbhistlot
                        WHERE idHistLot = {$idHistLot}";

            $row1 = $pessoal->select($select1, false);
            $idServidor = $row1["idServidor"];

            # Agora pega a lotação Posterior a esta mudança
            $select2 = "SELECT idHistLot,
                               lotacao
                          FROM tbhistlot 
                         WHERE idServidor = {$idServidor}
                      ORDER BY data DESC";

            $row2 = $pessoal->select($select2);

            $lotacao = null;
            foreach ($row2 as $item) {

                if ($item["idHistLot"] == $idHistLot) {
                    return $lotacao;
                } else {
                    $lotacao = $item["lotacao"];
                }
            }
        }
    }

    ###########################################################

    public function get_dataLotacaoPosterior($idHistLot = null) {        /**
         * Retorna a data inicial da lotação Posterior deste servidor a partir de uma mudança de lotação no histórico 
         * 
         * @syntax $this->getLotacaoAnterior($idRpa);
         */
        if (empty($idHistLot)) {
            return null;
        } else {
            $pessoal = new Pessoal();

            # Pega o servidor desta alteração de lotação
            $select1 = "SELECT idServidor
                          FROM tbhistlot
                         WHERE idHistLot = {$idHistLot}";

            $row1 = $pessoal->select($select1, false);                        
            $idServidor = $row1["idServidor"];

            # Agora pega a data Posterior a esta mudança
            $select2 = "SELECT idHistLot,
                               data
                          FROM tbhistlot 
                         WHERE idServidor = {$idServidor}
                      ORDER BY data DESC";
            
            $row2 = $pessoal->select($select2);

            $lotacao = null;
            foreach ($row2 as $item) {

                if ($item["idHistLot"] == $idHistLot) {
                    return $lotacao;
                } else {
                    $lotacao = date_to_php($item["data"]);
                }
            }
        }
    }

    ###########################################################

    public function getDataSaida($idHistLot = null) {
        /**
         * Retorna a data de saída de uma lotação
         * 
         * @syntax $this->getLotacaoAnterior($idRpa);
         */
        if (empty($idHistLot)) {
            return null;
        } else {
            $pessoal = new Pessoal();

            # Pega o servidor desta alteração de lotação
            $select1 = "SELECT idServidor
                         FROM tbhistlot
                        WHERE idHistLot = {$idHistLot}";

            $row1 = $pessoal->select($select1, false);
            $idServidor = $row1["idServidor"];

            # Agora pega a lotação posterior a esta mudança
            $select2 = "SELECT idHistLot,
                               data 
                          FROM tbhistlot 
                         WHERE idServidor = {$idServidor}
                      ORDER BY data DESC";

            $row2 = $pessoal->select($select2);

            $lotacao = null;
            foreach ($row2 as $item) {

                if ($item["idHistLot"] == $idHistLot) {


                    return date_to_php($lotacao);
                } else {
                    $lotacao = $item["data"];
                }
            }
        }
    }

    ###########################################################

    public function exibeHistoricoLotacao($idServidor = null) {
        /**
         * Retorna a lotação anterior deste servidor a partir de uma mudança de lotação no histórico 
         * 
         * @syntax $this->getLotacaoAnterior($idRpa);
         */
        if (empty($idServidor)) {
            return null;
        } else {
            $pessoal = new Pessoal();

            # Agora pega a lotação anterior a esta mudança
            $select = "SELECT data,
                              lotacao,
                              motivo
                         FROM tbhistlot 
                        WHERE idServidor = {$idServidor}
                     ORDER BY data";

            $row = $pessoal->select($select);
            $count = $pessoal->count($select);

            foreach ($row as $item) {
                plista(date_to_php($item["data"]) . " - " . $pessoal->get_nomeLotacao($item["lotacao"]), $item["motivo"]);
                $count--;
                if ($count > 0) {
                    hr();
                }
            }
        }
    }

    ###########################################################

    public function get_historicoLotacao($idServidor = null) {
        /**
         * Retorna um array com o histórico de lotação
         * 
         * @syntax $this->getLotacaoAnterior($idRpa);
         */
        if (empty($idServidor)) {
            return null;
        } else {
            $pessoal = new Pessoal();

            # Agora pega a lotação anterior a esta mudança
            $select = "SELECT *
                         FROM tbhistlot 
                        WHERE idServidor = {$idServidor}
                     ORDER BY data";

            $row = $pessoal->select($select);
            return $row;
        }
    }

    ###########################################################

    public function get_nomeDiretoria($idLotacao = null) {
        /**
         * retorna o nome completo da diretoria de um idLotação
         * 
         */
        if (empty($idLotacao)) {
            return null;
        } else {
            $pessoal = new Pessoal();
            $nome = null;

            # Cria a função caso não seja PHP 8
            if (!function_exists('str_contains')) {

                function str_contains($string = null, $search = null) {
                    /**
                     * Procura uma string dentro de outra string maior
                     */
                    if (empty($string)) {
                        return false;
                    }

                    if (empty($search)) {
                        return false;
                    }

                    if (preg_match("/{$search}/", $string)) {
                        return true;
                    } else {
                        return false;
                    }
                }

            }

            # Verifica a sigla dessa diretoria
            $select = "SELECT DIR
                       FROM tblotacao
                      WHERE idLotacao = {$idLotacao}";

            $row1 = $pessoal->select($select, false);
            $diretoria = $row1[0];

            # Pega a lotacao que pode ter o nome da diretoria
            $select = "SELECT GER, nome
                         FROM tblotacao
                        WHERE DIR = '{$diretoria}' 
                          AND (GER = 'SECR' OR GER = 'GAB')";

            $row2 = $pessoal->select($select, false);

            if (!empty($row2['nome'])) {
                # Verifica o nome
                if (str_contains($row2['GER'], 'SECR')) {
                    $nome = substr($row2['nome'], 14);
                } elseif (str_contains($row2['GER'], 'GAB')) {
                    $nome = substr($row2['nome'], 12);
                }
            }
            return $nome;
        }
    }

    ###########################################################

    public function get_nomeDiretoriaSigla($sigla = null) {
        /**
         * retorna o nome completo da diretoria de uma sigla
         * 
         */
        if (empty($sigla)) {
            return null;
        } else {
            $nome = null;

            # Cria a função caso não seja PHP 8
            if (!function_exists('str_contains')) {

                function str_contains($string = null, $search = null) {
                    /**
                     * Procura uma string dentro de outra string maior
                     */
                    if (empty($string)) {
                        return false;
                    }

                    if (empty($search)) {
                        return false;
                    }

                    if (preg_match("/{$search}/", $string)) {
                        return true;
                    } else {
                        return false;
                    }
                }

            }

            # Pega a lotacao que pode ter o nome da diretoria
            $select = "SELECT GER, nome
                         FROM tblotacao
                        WHERE DIR = '{$sigla}' 
                          AND (GER = 'SECR' OR GER = 'GAB')";

            $pessoal = new Pessoal();
            $row2 = $pessoal->select($select, false);

            if (!empty($row2['nome'])) {
                # Verifica o nome
                if (str_contains($row2['GER'], 'SECR')) {
                    $nome = substr($row2['nome'], 14);
                } elseif (str_contains($row2['GER'], 'GAB')) {
                    $nome = substr($row2['nome'], 12);
                }
            }

            if ($sigla == "Reitoria") {
                $nome = $sigla;
            }

            return $nome;
        }
    }

    ##########################################################################################

    function get_diretorSigla($sigla = null) {

        /**
         * 
         * Retorna o idServidor do diretor da lotação fornecida
         * 
         * @param $idLotacao integer o id da lotaçao
         * 
         */
        # Verifica o id
        if (empty($sigla)) {
            return null;
        }

        # Monta o select
        $select = "SELECT tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                          JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                          JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     LEFT JOIN tbcomissao ON (tbservidor.idServidor = tbcomissao.idServidor)
                                     LEFT JOIN tbtipocomissao ON (tbcomissao.idTipoComissao = tbtipocomissao.idTipoComissao)  
                    WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND tbcomissao.dtExo is null 
                     AND (tbtipocomissao.idTipoComissao = 16 OR tbtipocomissao.idTipoComissao = 15)
                     AND (tblotacao.dir = '$sigla')";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        if (empty($row[0])) {
            return null;
        } else {
            return $row[0];
        }
    }

    ##########################################################################################

    function get_proReitorSigla($sigla = null) {

        /**
         * 
         * Retorna o idServidor do proReitor da lotação fornecida
         * 
         * @param $idLotacao integer o id da lotaçao
         * 
         */
        # Verifica o id
        if (empty($sigla)) {
            return null;
        }

        # Monta o select
        $select = "SELECT tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                          JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                          JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     LEFT JOIN tbcomissao ON (tbservidor.idServidor = tbcomissao.idServidor)
                                     LEFT JOIN tbtipocomissao ON (tbcomissao.idTipoComissao = tbtipocomissao.idTipoComissao)  
                    WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND tbcomissao.dtExo is null 
                     AND tbtipocomissao.idTipoComissao = 15
                     AND (tblotacao.dir = '$sigla')";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        if (empty($row[0])) {
            return null;
        } else {
            return $row[0];
        }
    }

    ###########################################################

    public function getDataSaidaPraOnde($idHistLot = null) {
        /**
         * Retorna a data de saída de uma lotação
         * 
         * @syntax $this->getLotacaoAnterior($idRpa);
         */
        if (empty($idHistLot)) {
            return null;
        } else {
            if (empty($this->getLotacaoPosterior($idHistLot))) {
                pLista($this->getDataSaida($idHistLot));
            }

            $pessoal = new Pessoal();

            # Pega o servidor desta alteração de lotação
            $select1 = "SELECT idServidor
                         FROM tbhistlot
                        WHERE idHistLot = {$idHistLot}";

            $row = $pessoal->select($select1, false);
            $idServidor = $row["idServidor"];

            if (empty($this->getDataSaida($idHistLot))) {

                # Verifica se o servidor está ativo
                if ($pessoal->get_idSituacao($idServidor) <> 1) {
                    # Verifica se esta foi a últtima lotação dele
                    $select2 = "SELECT idHistLot
                                  FROM tbhistlot 
                                 WHERE idServidor = {$idServidor}
                              ORDER BY data DESC LIMIT 1";

                    $row2 = $pessoal->select($select2, false);

                    if ($idHistLot == $row2['idHistLot']) {
                        pLista($pessoal->get_dtSaida($idServidor), $pessoal->get_situacao($idServidor));
                    }
                }

                return null;
            } else {
                # Verifica se saiu para ser cedido
                if ($this->getLotacaoPosterior($idHistLot) == 113) {
                    $cessao = new Cessao();
                    pLista($this->getDataSaida($idHistLot), "cedido para:", $cessao->getOrgaoDtInicial($idServidor, $this->getDataSaida($idHistLot)));
                } else {
                    pLista($this->getDataSaida($idHistLot), "saiu para:", $this->getLotacao($this->getLotacaoPosterior($idHistLot)));
                }
            }
        }
    }

    ###########################################################

    public function getDataChegadaDeOnde($idHistLot = null) {
        /**
         * Retorna a data de saída de uma lotação
         * 
         * @syntax $this->getLotacaoAnterior($idRpa);
         */
        if (empty($idHistLot)) {
            return null;
        } else {

            $pessoal = new Pessoal();

            # Pega o servidor desta alteração de lotação
            $select1 = "SELECT idServidor
                         FROM tbhistlot
                        WHERE idHistLot = {$idHistLot}";

            $row = $pessoal->select($select1, false);
            $idServidor = $row["idServidor"];

            if (empty($this->getLotacaoAnterior($idHistLot))) {

                if ($pessoal->get_dtAdmissao($idServidor) == $this->getDataChegada($idHistLot)) {
                    pLista($this->getDataChegada($idHistLot), "Admissão");
                } else {
                    pLista($this->getDataChegada($idHistLot));
                }
            } else {
                # Verifica se saiu para ser cedido
                if ($this->getLotacaoAnterior($idHistLot) == 113) {
                    $cessao = new Cessao();
                    pLista($this->getDataChegada($idHistLot), "vindo da:", $cessao->getOrgaoDtFinal($idServidor, addDias($this->getDataChegada($idHistLot), $dias = -1, false)));
                } else {
                    pLista($this->getDataChegada($idHistLot), "vindo da:", $this->getLotacao($this->getLotacaoAnterior($idHistLot)));
                }
            }
        }
    }

    ###########################################################

    public function getDataChegada($idHistLot = null) {
        /**
         * Retorna a data de saída de uma lotação
         * 
         * @syntax $this->getLotacaoAnterior($idRpa);
         */
        if (empty($idHistLot)) {
            return null;
        } else {
            $pessoal = new Pessoal();

            # Pega o servidor desta alteração de lotação
            $select1 = "SELECT data
                          FROM tbhistlot
                         WHERE idHistLot = {$idHistLot}";

            $row = $pessoal->select($select1, false);
            return date_to_php($row["data"]);
        }
    }

    ###########################################################

    public function exibeNomeEmail($idLotacao = null) {
        /**
         * Exibe o nome da lotação e o e-mail cadastrado
         * 
         * @syntax $this->getLotacaoAnterior($idRpa);
         */
        if (empty($idLotacao)) {
            return null;
        } else {
            $pessoal = new Pessoal();

            # Pega o servidor desta alteração de lotação
            $select1 = "SELECT nome, email
                          FROM tblotacao
                         WHERE idLotacao = {$idLotacao}";

            $row = $pessoal->select($select1, false);
            plista(
                    $row["nome"],
                    espaco2br($row["email"])
            );
        }
    }

    ###########################################################

    public function get_email($idLotacao = null) {
        /**
         * Exibe o nome da lotação e o e-mail cadastrado
         * 
         * @syntax $this->getLotacaoAnterior($idRpa);
         */
        if (empty($idLotacao)) {
            return null;
        } else {
            $pessoal = new Pessoal();

            # Pega o servidor desta alteração de lotação
            $select = "SELECT email
                          FROM tblotacao ";

            # Lotacao
            # Verifica se o que veio é numérico
            if (is_numeric($idLotacao)) {
                $select .= " WHERE idlotacao = $idLotacao";
            } else { # senão é uma diretoria genérica
                $select .= " WHERE DIR = '$idLotacao'";
            }
            
            $row = $pessoal->select($select, false);
            return $row["email"];
        }
    }

    ###########################################################
}
