import React, {Component} from 'react';
import {Link} from 'react-router-dom';

class TaskList extends Component {
    state = {
        entries: []
    };

    componentDidMount() {
        new wp.api.collections.TaskNote().fetch().then((posts) => {
            this.setState({entries: posts});
        });
    }

    render() {
        return (
            <div className="task-list-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>TITLE</th>
                        <th>DATE</th>
                    </tr>
                    </thead>
                    <tbody>
                    {this.state.entries.map((taskNote) => {
                        return (
                            <tr>
                                <td>{taskNote.id}</td>
                                <td><Link to={'/' + taskNote.id}>{taskNote.title.rendered}</Link></td>
                                <td>{moment(taskNote.date).format('YYYY-MM-DD hh:mm a')}</td>
                            </tr>
                        );
                    })}
                    </tbody>
                </table>
                <p><Link to="/new-entry">새 업무 노트</Link></p>
            </div>
        );
    }
}

export default TaskList;
