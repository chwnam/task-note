import React, {Component} from 'react';
import {HashRouter as Router, Route, Switch} from "react-router-dom";
import Header from './components/Header';

import TaskList from './routes/TaskList';
import TaskEditEntry from './routes/TaskEditEntry';
import TaskNewEntry from './routes/TaskNewEntry';

class App extends Component {
    render() {
        return (
            <Router>
                <Header/>
                <Switch>
                    <Route exact path="/" component={TaskList}/>
                    <Route exact path="/new-entry" component={TaskNewEntry}/>
                    <Route exact path="/:id(\d+)" component={TaskEditEntry}/>
                </Switch>
            </Router>
        );
    }
}

export default App;
