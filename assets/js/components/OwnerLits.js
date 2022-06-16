// ./assets/js/components/OwnerLits.js

import React, {Component} from 'react';
import axios from 'axios';
//import {Route, Switch,Redirect, Link, withRouter} from 'react-router-dom';
//import Users from './Users';
//import Posts from './Posts';

class OwnerList extends Component {

    constructor() {
        super();
        this.state = { owners: [], loading: true}
    }

    componentDidMount() {
        this.getOwners();
    }

    getOwners() {
        axios.get(`http://127.0.0.1/api/owners`).then(res => {
            const owners = res.data.slice(0,999);
            this.setState({ owners, loading: false })
        })
    }

    render() {
        const loading = this.state.loading;
        const btEditAjaxPopupLink = `bt-edit ajax-popup-link`
        return (
            <div>
                {loading ? (
                    <div className={'row text-center'}><span className="fa fa-spin fa-spinner fa-4x"></span></div>
                ) : (
                    <div>
                        { this.state.owners.map(owner =>
                            <div className="list-user">
                                <div className="item" key={owner.id}>
                                    <div className="input-checkbox">
                                        <input type="checkbox" name="" data-user-pdf={owner.id}/>
                                    </div>
                                    <div className="title">
                                        <p>{owner.company} {owner.firstName} {owner.lastName}</p>
                                    </div>
                                    <div className="button">
                                        <a className="bt-invoice">Bill</a>
                                        <a className="bt-view ajax-popup-link">To see</a>
                                        <a href={`/edit-owner/owner-${ owner.id }`} className={btEditAjaxPopupLink} title={location.title}>Modifier</a>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                )}
            </div>
        )
    }
}

export default OwnerList;
