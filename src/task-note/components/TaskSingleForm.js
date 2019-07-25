import React, {Component} from 'react';
import {Link} from 'react-router-dom';
import DatePicker from 'react-datepicker';
// noinspection NpmUsedModulesInstalled
import wp from 'wp';
// noinspection NpmUsedModulesInstalled
import moment from 'moment';

import "react-datepicker/dist/react-datepicker.css";

class TaskSingleForm extends Component {
    state = {
        model: null,
        title: '',
        pickerDate: null,
        content: '',
    };

    isEdit() {
        return this.props.hasOwnProperty('match') && this.props.match.params.id;
    }

    componentDidMount() {
        if (this.isEdit()) {
            let model = new wp.api.models.TaskNote({
                id: this.props.match.params.id
            });

            model.fetch({
                data: {context: 'edit'}
            }).then((entry) => {
                this.setState({
                    pickerDate: entry.date ? moment(entry.date).toDate() : null,
                    title: entry.title.raw,
                    content: entry.content.raw
                });
            });

            this.setState({model: model});
        } else {
            this.setState({model: new wp.api.models.TaskNote()});
        }
    }

    handleDatePickerChange(date) {
        date.setHours(0, 0, 0);
        this.setState({pickerDate: date});
    }

    handleTitleChange(e) {
        this.setState({title: e.target.value});
    }

    handleContentChange(e) {
        this.setState({content: e.target.value});
    }

    handleButtonSubmit(e) {
        let pickerDate = moment(this.state.pickerDate).format('YYYY-MM-DD HH:mm:ss');

        this.state.model
            .set('title', this.state.title)
            .set('content', this.state.content)
            .set('date', pickerDate)
            .set('status', 'publish')
            .save()
            .then(() => {
                alert('저장되었습니다.');
            });
    }

    render() {
        return (
            <div>
                <table>
                    <tr>
                        <th scope="row">ID</th>
                        <td>{this.state.model && this.state.model.get('id')}</td>
                    </tr>
                    <tr>
                        <th scope="row">DATE</th>
                        <td>
                            <DatePicker
                                dateFormat="yyyy-MM-dd"
                                selected={this.state.pickerDate}
                                onChange={::this.handleDatePickerChange}
                            />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">TITLE</th>
                        <td>
                            <input
                                type="text"
                                value={this.state.title}
                                onChange={::this.handleTitleChange}
                            />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">CONTENT</th>
                        <td>
                            <textarea
                                rows="4"
                                cols="40"
                                value={this.state.content}
                                onChange={::this.handleContentChange}
                            />
                        </td>
                    </tr>
                </table>
                <ul>
                    <li>
                        <button
                            className="button button-primary"
                            onClick={::this.handleButtonSubmit}
                        >저장하기
                        </button>
                    </li>
                    <li><Link to="/">목록으로</Link></li>
                </ul>
            </div>
        );
    }
}

export default TaskSingleForm;
