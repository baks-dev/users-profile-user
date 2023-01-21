/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

(() => {
    var fe = Object.create;
    var J = Object.defineProperty;
    var pe = Object.getOwnPropertyDescriptor;
    var de = Object.getOwnPropertyNames, G = Object.getOwnPropertySymbols, _e = Object.getPrototypeOf,
        V = Object.prototype.hasOwnProperty, be = Object.prototype.propertyIsEnumerable;
    var Q = (o, s, e) => s in o ? J(o, s, {enumerable: !0, configurable: !0, writable: !0, value: e}) : o[s] = e,
        q = (o, s) => {
            for (var e in s || (s = {})) V.call(s, e) && Q(o, e, s[e]);
            if (G) for (var e of G(s)) be.call(s, e) && Q(o, e, s[e]);
            return o
        };
    var me = (o, s) => () => (s || o((s = {exports: {}}).exports, s), s.exports);
    var ge = (o, s, e, t) => {
        if (s && typeof s == "object" || typeof s == "function") for (let n of de(s)) !V.call(o, n) && n !== e && J(o, n, {
            get: () => s[n],
            enumerable: !(t = pe(s, n)) || t.enumerable
        });
        return o
    };
    var X = (o, s, e) => (e = o != null ? fe(_e(o)) : {}, ge(s || !o || !o.__esModule ? J(e, "default", {
        value: o,
        enumerable: !0
    }) : e, o));
    var E = (o, s, e) => new Promise((t, n) => {
        var i = c => {
            try {
                a(e.next(c))
            } catch (l) {
                n(l)
            }
        }, r = c => {
            try {
                a(e.throw(c))
            } catch (l) {
                n(l)
            }
        }, a = c => c.done ? t(c.value) : Promise.resolve(c.value).then(i, r);
        a((e = e.apply(o, s)).next())
    });
    var H = me((Re, F) => {
        "use strict";
        var y = typeof Reflect == "object" ? Reflect : null,
            Y = y && typeof y.apply == "function" ? y.apply : function (s, e, t) {
                return Function.prototype.apply.call(s, e, t)
            }, R;
        y && typeof y.ownKeys == "function" ? R = y.ownKeys : Object.getOwnPropertySymbols ? R = function (s) {
            return Object.getOwnPropertyNames(s).concat(Object.getOwnPropertySymbols(s))
        } : R = function (s) {
            return Object.getOwnPropertyNames(s)
        };

        function ve(o) {
            console && console.warn && console.warn(o)
        }

        var $ = Number.isNaN || function (s) {
            return s !== s
        };

        function u() {
            u.init.call(this)
        }

        F.exports = u;
        F.exports.once = Ce;
        u.EventEmitter = u;
        u.prototype._events = void 0;
        u.prototype._eventsCount = 0;
        u.prototype._maxListeners = void 0;
        var Z = 10;

        function k(o) {
            if (typeof o != "function") throw new TypeError('The "listener" argument must be of type Function. Received type ' + typeof o)
        }

        Object.defineProperty(u, "defaultMaxListeners", {
            enumerable: !0, get: function () {
                return Z
            }, set: function (o) {
                if (typeof o != "number" || o < 0 || $(o)) throw new RangeError('The value of "defaultMaxListeners" is out of range. It must be a non-negative number. Received ' + o + ".");
                Z = o
            }
        });
        u.init = function () {
            (this._events === void 0 || this._events === Object.getPrototypeOf(this)._events) && (this._events = Object.create(null), this._eventsCount = 0), this._maxListeners = this._maxListeners || void 0
        };
        u.prototype.setMaxListeners = function (s) {
            if (typeof s != "number" || s < 0 || $(s)) throw new RangeError('The value of "n" is out of range. It must be a non-negative number. Received ' + s + ".");
            return this._maxListeners = s, this
        };

        function ee(o) {
            return o._maxListeners === void 0 ? u.defaultMaxListeners : o._maxListeners
        }

        u.prototype.getMaxListeners = function () {
            return ee(this)
        };
        u.prototype.emit = function (s) {
            for (var e = [], t = 1; t < arguments.length; t++) e.push(arguments[t]);
            var n = s === "error", i = this._events;
            if (i !== void 0) n = n && i.error === void 0; else if (!n) return !1;
            if (n) {
                var r;
                if (e.length > 0 && (r = e[0]), r instanceof Error) throw r;
                var a = new Error("Unhandled error." + (r ? " (" + r.message + ")" : ""));
                throw a.context = r, a
            }
            var c = i[s];
            if (c === void 0) return !1;
            if (typeof c == "function") Y(c, this, e); else for (var l = c.length, b = re(c, l), t = 0; t < l; ++t) Y(b[t], this, e);
            return !0
        };

        function te(o, s, e, t) {
            var n, i, r;
            if (k(e), i = o._events, i === void 0 ? (i = o._events = Object.create(null), o._eventsCount = 0) : (i.newListener !== void 0 && (o.emit("newListener", s, e.listener ? e.listener : e), i = o._events), r = i[s]), r === void 0) r = i[s] = e, ++o._eventsCount; else if (typeof r == "function" ? r = i[s] = t ? [e, r] : [r, e] : t ? r.unshift(e) : r.push(e), n = ee(o), n > 0 && r.length > n && !r.warned) {
                r.warned = !0;
                var a = new Error("Possible EventEmitter memory leak detected. " + r.length + " " + String(s) + " listeners added. Use emitter.setMaxListeners() to increase limit");
                a.name = "MaxListenersExceededWarning", a.emitter = o, a.type = s, a.count = r.length, ve(a)
            }
            return o
        }

        u.prototype.addListener = function (s, e) {
            return te(this, s, e, !1)
        };
        u.prototype.on = u.prototype.addListener;
        u.prototype.prependListener = function (s, e) {
            return te(this, s, e, !0)
        };

        function ye() {
            if (!this.fired) return this.target.removeListener(this.type, this.wrapFn), this.fired = !0, arguments.length === 0 ? this.listener.call(this.target) : this.listener.apply(this.target, arguments)
        }

        function ne(o, s, e) {
            var t = {fired: !1, wrapFn: void 0, target: o, type: s, listener: e}, n = ye.bind(t);
            return n.listener = e, t.wrapFn = n, n
        }

        u.prototype.once = function (s, e) {
            return k(e), this.on(s, ne(this, s, e)), this
        };
        u.prototype.prependOnceListener = function (s, e) {
            return k(e), this.prependListener(s, ne(this, s, e)), this
        };
        u.prototype.removeListener = function (s, e) {
            var t, n, i, r, a;
            if (k(e), n = this._events, n === void 0) return this;
            if (t = n[s], t === void 0) return this;
            if (t === e || t.listener === e) --this._eventsCount === 0 ? this._events = Object.create(null) : (delete n[s], n.removeListener && this.emit("removeListener", s, t.listener || e)); else if (typeof t != "function") {
                for (i = -1, r = t.length - 1; r >= 0; r--) if (t[r] === e || t[r].listener === e) {
                    a = t[r].listener, i = r;
                    break
                }
                if (i < 0) return this;
                i === 0 ? t.shift() : Se(t, i), t.length === 1 && (n[s] = t[0]), n.removeListener !== void 0 && this.emit("removeListener", s, a || e)
            }
            return this
        };
        u.prototype.off = u.prototype.removeListener;
        u.prototype.removeAllListeners = function (s) {
            var e, t, n;
            if (t = this._events, t === void 0) return this;
            if (t.removeListener === void 0) return arguments.length === 0 ? (this._events = Object.create(null), this._eventsCount = 0) : t[s] !== void 0 && (--this._eventsCount === 0 ? this._events = Object.create(null) : delete t[s]), this;
            if (arguments.length === 0) {
                var i = Object.keys(t), r;
                for (n = 0; n < i.length; ++n) r = i[n], r !== "removeListener" && this.removeAllListeners(r);
                return this.removeAllListeners("removeListener"), this._events = Object.create(null), this._eventsCount = 0, this
            }
            if (e = t[s], typeof e == "function") this.removeListener(s, e); else if (e !== void 0) for (n = e.length - 1; n >= 0; n--) this.removeListener(s, e[n]);
            return this
        };

        function se(o, s, e) {
            var t = o._events;
            if (t === void 0) return [];
            var n = t[s];
            return n === void 0 ? [] : typeof n == "function" ? e ? [n.listener || n] : [n] : e ? xe(n) : re(n, n.length)
        }

        u.prototype.listeners = function (s) {
            return se(this, s, !0)
        };
        u.prototype.rawListeners = function (s) {
            return se(this, s, !1)
        };
        u.listenerCount = function (o, s) {
            return typeof o.listenerCount == "function" ? o.listenerCount(s) : ie.call(o, s)
        };
        u.prototype.listenerCount = ie;

        function ie(o) {
            var s = this._events;
            if (s !== void 0) {
                var e = s[o];
                if (typeof e == "function") return 1;
                if (e !== void 0) return e.length
            }
            return 0
        }

        u.prototype.eventNames = function () {
            return this._eventsCount > 0 ? R(this._events) : []
        };

        function re(o, s) {
            for (var e = new Array(s), t = 0; t < s; ++t) e[t] = o[t];
            return e
        }

        function Se(o, s) {
            for (; s + 1 < o.length; s++) o[s] = o[s + 1];
            o.pop()
        }

        function xe(o) {
            for (var s = new Array(o.length), e = 0; e < s.length; ++e) s[e] = o[e].listener || o[e];
            return s
        }

        function Ce(o, s) {
            return new Promise(function (e, t) {
                function n(r) {
                    o.removeListener(s, i), t(r)
                }

                function i() {
                    typeof o.removeListener == "function" && o.removeListener("error", n), e([].slice.call(arguments))
                }

                oe(o, s, i, {once: !0}), s !== "error" && Ee(o, n, {once: !0})
            })
        }

        function Ee(o, s, e) {
            typeof o.on == "function" && oe(o, "error", s, e)
        }

        function oe(o, s, e, t) {
            if (typeof o.on == "function") t.once ? o.once(s, e) : o.on(s, e); else if (typeof o.addEventListener == "function") o.addEventListener(s, function n(i) {
                t.once && o.removeEventListener(s, n), e(i)
            }); else throw new TypeError('The "emitter" argument must be of type EventEmitter. Received type ' + typeof o)
        }
    });
    var le = X(H());
    var p = {
            timeout: 1,
            transportClosed: 2,
            clientDisconnected: 3,
            clientClosed: 4,
            clientConnectToken: 5,
            clientRefreshToken: 6,
            subscriptionUnsubscribed: 7,
            subscriptionSubscribeToken: 8,
            subscriptionRefreshToken: 9,
            transportWriteError: 10,
            connectionClosed: 11
        }, v = {connectCalled: 0, transportClosed: 1, noPing: 2, subscribeTimeout: 3, unsubscribeError: 4},
        O = {disconnectCalled: 0, unauthorized: 1, badProtocol: 2, messageSizeLimit: 3},
        L = {subscribeCalled: 0, transportClosed: 1}, B = {unsubscribeCalled: 0, unauthorized: 1, clientClosed: 2};
    var j = (t => (t.Disconnected = "disconnected", t.Connecting = "connecting", t.Connected = "connected", t))(j || {}),
        D = (t => (t.Unsubscribed = "unsubscribed", t.Subscribing = "subscribing", t.Subscribed = "subscribed", t))(D || {});

    function ae(o, s) {
        return o.lastIndexOf(s, 0) === 0
    }

    function K(o) {
        return o == null ? !1 : typeof o == "function"
    }

    function ce(o, s) {
        if (window.console) {
            let e = window.console[o];
            K(e) && e.apply(window.console, s)
        }
    }

    function Te(o, s) {
        return Math.floor(Math.random() * (s - o + 1) + o)
    }

    function S(o, s, e) {
        o > 31 && (o = 31);
        let t = Te(0, Math.min(e, s * Math.pow(2, o)));
        return Math.min(e, s + t)
    }

    function ue(o) {
        return "error" in o && o.error !== null
    }

    function x(o) {
        return Math.min(o * 1e3, 2147483647)
    }

    var I = class extends le.default {
        constructor(e, t, n) {
            super();
            this._resubscribeTimeout = null;
            this._refreshTimeout = null;
            this.channel = t, this.state = "unsubscribed", this._centrifuge = e, this._token = null, this._getToken = null, this._data = null, this._recover = !1, this._offset = null, this._epoch = null, this._recoverable = !1, this._positioned = !1, this._joinLeave = !1, this._minResubscribeDelay = 500, this._maxResubscribeDelay = 2e4, this._resubscribeTimeout = null, this._resubscribeAttempts = 0, this._promises = {}, this._promiseId = 0, this._inflight = !1, this._refreshTimeout = null, this._setOptions(n), this._centrifuge._debugEnabled ? (this.on("state", i => {
                this._centrifuge._debug("subscription state", t, i.oldState, "->", i.newState)
            }), this.on("error", i => {
                this._centrifuge._debug("subscription error", t, i)
            })) : this.on("error", function () {
                Function.prototype()
            })
        }

        ready(e) {
            return this.state === "unsubscribed" ? Promise.reject({
                code: p.subscriptionUnsubscribed,
                message: this.state
            }) : this.state === "subscribed" ? Promise.resolve() : new Promise((t, n) => {
                let i = {resolve: t, reject: n};
                e && (i.timeout = setTimeout(function () {
                    n({code: p.timeout, message: "timeout"})
                }, e)), this._promises[this._nextPromiseId()] = i
            })
        }

        subscribe() {
            this._isSubscribed() || (this._resubscribeAttempts = 0, this._setSubscribing(L.subscribeCalled, "subscribe called"))
        }

        unsubscribe() {
            this._setUnsubscribed(B.unsubscribeCalled, "unsubscribe called", !0)
        }

        publish(e) {
            let t = this;
            return this._methodCall().then(function () {
                return t._centrifuge.publish(t.channel, e)
            })
        }

        presence() {
            let e = this;
            return this._methodCall().then(function () {
                return e._centrifuge.presence(e.channel)
            })
        }

        presenceStats() {
            let e = this;
            return this._methodCall().then(function () {
                return e._centrifuge.presenceStats(e.channel)
            })
        }

        history(e) {
            let t = this;
            return this._methodCall().then(function () {
                return t._centrifuge.history(t.channel, e)
            })
        }

        _methodCall() {
            return this._isSubscribed() ? Promise.resolve() : this._isUnsubscribed() ? Promise.reject({
                code: p.subscriptionUnsubscribed,
                message: this.state
            }) : new Promise((e, t) => {
                let n = setTimeout(function () {
                    t({code: p.timeout, message: "timeout"})
                }, this._centrifuge._config.timeout);
                this._promises[this._nextPromiseId()] = {timeout: n, resolve: e, reject: t}
            })
        }

        _nextPromiseId() {
            return ++this._promiseId
        }

        _needRecover() {
            return this._recover === !0
        }

        _isUnsubscribed() {
            return this.state === "unsubscribed"
        }

        _isSubscribing() {
            return this.state === "subscribing"
        }

        _isSubscribed() {
            return this.state === "subscribed"
        }

        _setState(e) {
            if (this.state !== e) {
                let t = this.state;
                return this.state = e, this.emit("state", {newState: e, oldState: t, channel: this.channel}), !0
            }
            return !1
        }

        _usesToken() {
            return this._token !== null || this._getToken !== null
        }

        _clearSubscribingState() {
            this._resubscribeAttempts = 0, this._clearResubscribeTimeout()
        }

        _clearSubscribedState() {
            this._clearRefreshTimeout()
        }

        _setSubscribed(e) {
            if (!this._isSubscribing()) return;
            this._clearSubscribingState(), e.recoverable && (this._recover = !0, this._offset = e.offset || 0, this._epoch = e.epoch || ""), this._setState("subscribed");
            let t = this._centrifuge._getSubscribeContext(this.channel, e);
            this.emit("subscribed", t), this._resolvePromises();
            let n = e.publications;
            if (n && n.length > 0) for (let i in n) !n.hasOwnProperty(i) || this._handlePublication(n[i]);
            e.expires === !0 && (this._refreshTimeout = setTimeout(() => this._refresh(), x(e.ttl)))
        }

        _setSubscribing(e, t) {
            this._isSubscribing() || (this._isSubscribed() && this._clearSubscribedState(), this._setState("subscribing") && this.emit("subscribing", {
                channel: this.channel,
                code: e,
                reason: t
            }), this._subscribe(!1, !1))
        }

        _subscribe(e, t) {
            if (this._centrifuge._debug("subscribing on", this.channel), this._centrifuge.state !== "connected" && !e) return this._centrifuge._debug("delay subscribe on", this.channel, "till connected"), null;
            if (this._usesToken()) {
                if (this._token) return this._sendSubscribe(this._token, t);
                {
                    if (e) return null;
                    let n = this;
                    return this._getSubscriptionToken().then(function (i) {
                        if (!!n._isSubscribing()) {
                            if (!i) {
                                n._failUnauthorized();
                                return
                            }
                            n._token = i, n._sendSubscribe(i, !1)
                        }
                    }).catch(function (i) {
                        !n._isSubscribing() || (n.emit("error", {
                            type: "subscribeToken",
                            channel: n.channel,
                            error: {code: p.subscriptionSubscribeToken, message: i !== void 0 ? i.toString() : ""}
                        }), n._scheduleResubscribe())
                    }), null
                }
            } else return this._sendSubscribe("", t)
        }

        _sendSubscribe(e, t) {
            let i = {channel: this.channel};
            if (e && (i.token = e), this._data && (i.data = this._data), this._positioned && (i.positioned = !0), this._recoverable && (i.recoverable = !0), this._joinLeave && (i.join_leave = !0), this._needRecover()) {
                i.recover = !0;
                let a = this._getOffset();
                a && (i.offset = a);
                let c = this._getEpoch();
                c && (i.epoch = c)
            }
            let r = {subscribe: i};
            return this._inflight = !0, this._centrifuge._call(r, t).then(a => {
                this._inflight = !1;
                let c = a.reply.subscribe;
                this._handleSubscribeResponse(c), a.next && a.next()
            }, a => {
                this._inflight = !1, this._handleSubscribeError(a.error), a.next && a.next()
            }), r
        }

        _handleSubscribeError(e) {
            if (!!this._isSubscribing()) {
                if (e.code === p.timeout) {
                    this._centrifuge._disconnect(v.subscribeTimeout, "subscribe timeout", !0);
                    return
                }
                this._subscribeError(e)
            }
        }

        _handleSubscribeResponse(e) {
            !this._isSubscribing() || this._setSubscribed(e)
        }

        _setUnsubscribed(e, t, n) {
            this._isUnsubscribed() || (this._isSubscribed() && (n && this._centrifuge._unsubscribe(this), this._clearSubscribedState()), this._isSubscribing() && this._clearSubscribingState(), this._setState("unsubscribed") && this.emit("unsubscribed", {
                channel: this.channel,
                code: e,
                reason: t
            }), this._rejectPromises({code: p.subscriptionUnsubscribed, message: this.state}))
        }

        _handlePublication(e) {
            let t = this._centrifuge._getPublicationContext(this.channel, e);
            this.emit("publication", t), e.offset && (this._offset = e.offset)
        }

        _handleJoin(e) {
            let t = this._centrifuge._getJoinLeaveContext(e.info);
            this.emit("join", {channel: this.channel, info: t})
        }

        _handleLeave(e) {
            let t = this._centrifuge._getJoinLeaveContext(e.info);
            this.emit("leave", {channel: this.channel, info: t})
        }

        _resolvePromises() {
            for (let e in this._promises) this._promises[e].timeout && clearTimeout(this._promises[e].timeout), this._promises[e].resolve(), delete this._promises[e]
        }

        _rejectPromises(e) {
            for (let t in this._promises) this._promises[t].timeout && clearTimeout(this._promises[t].timeout), this._promises[t].reject(e), delete this._promises[t]
        }

        _scheduleResubscribe() {
            let e = this, t = this._getResubscribeDelay();
            this._resubscribeTimeout = setTimeout(function () {
                e._isSubscribing() && e._subscribe(!1, !1)
            }, t)
        }

        _subscribeError(e) {
            if (!!this._isSubscribing()) if (e.code < 100 || e.code === 109 || e.temporary === !0) {
                e.code === 109 && (this._token = null);
                let t = {channel: this.channel, type: "subscribe", error: e};
                this._centrifuge.state === "connected" && this.emit("error", t), this._scheduleResubscribe()
            } else this._setUnsubscribed(e.code, e.message, !1)
        }

        _getResubscribeDelay() {
            let e = S(this._resubscribeAttempts, this._minResubscribeDelay, this._maxResubscribeDelay);
            return this._resubscribeAttempts++, e
        }

        _setOptions(e) {
            !e || (e.since && (this._offset = e.since.offset, this._epoch = e.since.epoch, this._recover = !0), e.data && (this._data = e.data), e.minResubscribeDelay !== void 0 && (this._minResubscribeDelay = e.minResubscribeDelay), e.maxResubscribeDelay !== void 0 && (this._maxResubscribeDelay = e.maxResubscribeDelay), e.token && (this._token = e.token), e.getToken && (this._getToken = e.getToken), e.positioned === !0 && (this._positioned = !0), e.recoverable === !0 && (this._recoverable = !0), e.joinLeave === !0 && (this._joinLeave = !0))
        }

        _getOffset() {
            let e = this._offset;
            return e !== null ? e : 0
        }

        _getEpoch() {
            let e = this._epoch;
            return e !== null ? e : ""
        }

        _clearRefreshTimeout() {
            this._refreshTimeout !== null && (clearTimeout(this._refreshTimeout), this._refreshTimeout = null)
        }

        _clearResubscribeTimeout() {
            this._resubscribeTimeout !== null && (clearTimeout(this._resubscribeTimeout), this._resubscribeTimeout = null)
        }

        _getSubscriptionToken() {
            this._centrifuge._debug("get subscription token for channel", this.channel);
            let e = {channel: this.channel}, t = this._getToken;
            if (t === null) throw new Error("provide a function to get channel subscription token");
            return t(e)
        }

        _refresh() {
            this._clearRefreshTimeout();
            let e = this;
            this._getSubscriptionToken().then(function (t) {
                if (!e._isSubscribed()) return;
                if (!t) {
                    e._failUnauthorized();
                    return
                }
                e._token = t;
                let i = {sub_refresh: {channel: e.channel, token: t}};
                e._centrifuge._call(i).then(r => {
                    let a = r.reply.sub_refresh;
                    e._refreshResponse(a), r.next && r.next()
                }, r => {
                    e._refreshError(r.error), r.next && r.next()
                })
            }).catch(function (t) {
                e.emit("error", {
                    type: "refreshToken",
                    channel: e.channel,
                    error: {code: p.subscriptionRefreshToken, message: t !== void 0 ? t.toString() : ""}
                }), e._refreshTimeout = setTimeout(() => e._refresh(), e._getRefreshRetryDelay())
            })
        }

        _refreshResponse(e) {
            !this._isSubscribed() || (this._centrifuge._debug("subscription token refreshed, channel", this.channel), this._clearRefreshTimeout(), e.expires === !0 && (this._refreshTimeout = setTimeout(() => this._refresh(), x(e.ttl))))
        }

        _refreshError(e) {
            !this._isSubscribed() || (e.code < 100 || e.temporary === !0 ? (this.emit("error", {
                type: "refresh",
                channel: this.channel,
                error: e
            }), this._refreshTimeout = setTimeout(() => this._refresh(), this._getRefreshRetryDelay())) : this._setUnsubscribed(e.code, e.message, !0))
        }

        _getRefreshRetryDelay() {
            return S(0, 1e4, 2e4)
        }

        _failUnauthorized() {
            this._setUnsubscribed(B.unauthorized, "unauthorized", !0)
        }
    };
    var U = class {
        constructor(s, e) {
            this.endpoint = s, this.options = e, this._transport = null
        }

        name() {
            return "sockjs"
        }

        subName() {
            return "sockjs-" + this._transport.transport
        }

        emulation() {
            return !1
        }

        supported() {
            return this.options.sockjs !== null
        }

        initialize(s, e) {
            this._transport = new this.options.sockjs(this.endpoint, null, this.options.sockjsOptions), this._transport.onopen = () => {
                e.onOpen()
            }, this._transport.onerror = t => {
                e.onError(t)
            }, this._transport.onclose = t => {
                e.onClose(t)
            }, this._transport.onmessage = t => {
                e.onMessage(t.data)
            }
        }

        close() {
            this._transport.close()
        }

        send(s) {
            this._transport.send(s)
        }
    };
    var T = class {
        constructor(s, e) {
            this.endpoint = s, this.options = e, this._transport = null
        }

        name() {
            return "websocket"
        }

        subName() {
            return "websocket"
        }

        emulation() {
            return !1
        }

        supported() {
            return this.options.websocket !== void 0 && this.options.websocket !== null
        }

        initialize(s, e) {
            let t = "";
            s === "protobuf" && (t = "centrifuge-protobuf"), t !== "" ? this._transport = new this.options.websocket(this.endpoint, t) : this._transport = new this.options.websocket(this.endpoint), s === "protobuf" && (this._transport.binaryType = "arraybuffer"), this._transport.onopen = () => {
                e.onOpen()
            }, this._transport.onerror = n => {
                e.onError(n)
            }, this._transport.onclose = n => {
                e.onClose(n)
            }, this._transport.onmessage = n => {
                e.onMessage(n.data)
            }
        }

        close() {
            this._transport.close()
        }

        send(s) {
            this._transport.send(s)
        }
    };
    var A = class {
        constructor(s, e) {
            this.endpoint = s, this.options = e, this._abortController = null, this._utf8decoder = new TextDecoder, this._protocol = "json"
        }

        name() {
            return "http_stream"
        }

        subName() {
            return "http_stream"
        }

        emulation() {
            return !0
        }

        _handleErrors(s) {
            if (!s.ok) throw new Error(s.status);
            return s
        }

        _fetchEventTarget(s, e, t) {
            let n = new EventTarget;
            return s.options.fetch(e, t).then(s._handleErrors).then(r => {
                n.dispatchEvent(new Event("open"));
                let a = "", c = 0, l = new Uint8Array, b = r.body.getReader();
                return new s.options.readableStream({
                    start(m) {
                        function w() {
                            return b.read().then(({done: h, value: _}) => {
                                if (h) {
                                    n.dispatchEvent(new Event("close")), m.close();
                                    return
                                }
                                try {
                                    if (s._protocol === "json") for (a += s._utf8decoder.decode(_); c < a.length;) if (a[c] === `
`) {
                                        let f = a.substring(0, c);
                                        n.dispatchEvent(new MessageEvent("message", {data: f})), a = a.substring(c + 1), c = 0
                                    } else ++c; else {
                                        let f = new Uint8Array(l.length + _.length);
                                        for (f.set(l), f.set(_, l.length), l = f; ;) {
                                            let d = s.options.decoder.decodeReply(l);
                                            if (d.ok) {
                                                let P = l.slice(0, d.pos);
                                                n.dispatchEvent(new MessageEvent("message", {data: P})), l = l.slice(d.pos);
                                                continue
                                            }
                                            break
                                        }
                                    }
                                } catch (f) {
                                    n.dispatchEvent(new Event("error", {detail: f})), n.dispatchEvent(new Event("close")), m.close();
                                    return
                                }
                                w()
                            }).catch(function (h) {
                                n.dispatchEvent(new Event("error", {detail: h})), n.dispatchEvent(new Event("close")), m.close()
                            })
                        }

                        return w()
                    }
                })
            }).catch(r => {
                n.dispatchEvent(new Event("error", {detail: r})), n.dispatchEvent(new Event("close"))
            }), n
        }

        supported() {
            return this.options.fetch !== null && this.options.readableStream !== null && typeof TextDecoder != "undefined" && typeof AbortController != "undefined" && typeof EventTarget != "undefined" && typeof Event != "undefined" && typeof MessageEvent != "undefined" && typeof Error != "undefined"
        }

        initialize(s, e, t) {
            this._protocol = s, this._abortController = new AbortController;
            let n, i;
            s === "json" ? (n = {
                Accept: "application/json",
                "Content-Type": "application/json"
            }, i = t) : (n = {Accept: "application/octet-stream", "Content-Type": "application/octet-stream"}, i = t);
            let r = {
                method: "POST",
                headers: n,
                body: i,
                mode: "cors",
                credentials: "same-origin",
                cache: "no-cache",
                signal: this._abortController.signal
            }, a = this._fetchEventTarget(this, this.endpoint, r);
            a.addEventListener("open", () => {
                e.onOpen()
            }), a.addEventListener("error", c => {
                this._abortController.abort(), e.onError(c)
            }), a.addEventListener("close", () => {
                this._abortController.abort(), e.onClose({code: 4, reason: "connection closed"})
            }), a.addEventListener("message", c => {
                e.onMessage(c.data)
            })
        }

        close() {
            this._abortController.abort()
        }

        send(s, e, t) {
            let n, i, r = {session: e, node: t, data: s};
            this._protocol === "json" ? (n = {"Content-Type": "application/json"}, i = JSON.stringify(r)) : (n = {"Content-Type": "application/octet-stream"}, i = this.options.encoder.encodeEmulationRequest(r));
            let a = this.options.fetch,
                c = {method: "POST", headers: n, body: i, mode: "cors", credentials: "same-origin", cache: "no-cache"};
            a(this.options.emulationEndpoint, c)
        }
    };
    var M = class {
        constructor(s, e) {
            this.endpoint = s, this.options = e, this._protocol = "json", this._transport = null, this._onClose = null
        }

        name() {
            return "sse"
        }

        subName() {
            return "sse"
        }

        emulation() {
            return !0
        }

        supported() {
            return this.options.eventsource !== null && this.options.fetch !== null
        }

        initialize(s, e, t) {
            let n;
            window && window.document && window.document.baseURI ? n = new URL(this.endpoint, window.document.baseURI) : n = new URL(this.endpoint), n.searchParams.append("cf_connect", t);
            let i = {}, r = new this.options.eventsource(n.toString(), i);
            this._transport = r;
            let a = this;
            r.onopen = function () {
                e.onOpen()
            }, r.onerror = function (c) {
                r.close(), e.onError(c), e.onClose({code: 4, reason: "connection closed"})
            }, r.onmessage = function (c) {
                e.onMessage(c.data)
            }, a._onClose = function () {
                e.onClose({code: 4, reason: "connection closed"})
            }
        }

        close() {
            this._transport.close(), this._onClose !== null && this._onClose()
        }

        send(s, e, t) {
            let n = {session: e, node: t, data: s}, i = {"Content-Type": "application/json"}, r = JSON.stringify(n),
                a = this.options.fetch,
                c = {method: "POST", headers: i, body: r, mode: "cors", credentials: "same-origin", cache: "no-cache"};
            a(this.options.emulationEndpoint, c)
        }
    };
    var N = class {
        constructor(s, e) {
            this.endpoint = s, this.options = e, this._transport = null, this._stream = null, this._writer = null, this._utf8decoder = new TextDecoder, this._protocol = "json"
        }

        name() {
            return "webtransport"
        }

        subName() {
            return "webtransport"
        }

        emulation() {
            return !1
        }

        supported() {
            return this.options.webtransport !== void 0 && this.options.webtransport !== null
        }

        initialize(s, e) {
            return E(this, null, function* () {
                let t;
                window && window.document && window.document.baseURI ? t = new URL(this.endpoint, window.document.baseURI) : t = new URL(this.endpoint), s === "protobuf" && t.searchParams.append("cf_protocol", "protobuf"), this._protocol = s;
                let n = new EventTarget;
                this._transport = new this.options.webtransport(t.toString()), this._transport.closed.then(() => {
                    e.onClose({code: 4, reason: "connection closed"})
                }).catch(() => {
                    e.onClose({code: 4, reason: "connection closed"})
                });
                try {
                    yield this._transport.ready
                } catch (r) {
                    this.close();
                    return
                }
                let i;
                try {
                    i = yield this._transport.createBidirectionalStream()
                } catch (r) {
                    this.close();
                    return
                }
                this._stream = i, this._writer = this._stream.writable.getWriter(), n.addEventListener("close", () => {
                    e.onClose({code: 4, reason: "connection closed"})
                }), n.addEventListener("message", r => {
                    e.onMessage(r.data)
                }), this._startReading(n), e.onOpen()
            })
        }

        _startReading(s) {
            return E(this, null, function* () {
                let e = this._stream.readable.getReader(), t = "", n = 0, i = new Uint8Array;
                try {
                    for (; ;) {
                        let {done: r, value: a} = yield e.read();
                        if (a.length > 0) if (this._protocol === "json") for (t += this._utf8decoder.decode(a); n < t.length;) if (t[n] === `
`) {
                            let c = t.substring(0, n);
                            s.dispatchEvent(new MessageEvent("message", {data: c})), t = t.substring(n + 1), n = 0
                        } else ++n; else {
                            let c = new Uint8Array(i.length + a.length);
                            for (c.set(i), c.set(a, i.length), i = c; ;) {
                                let l = this.options.decoder.decodeReply(i);
                                if (l.ok) {
                                    let b = i.slice(0, l.pos);
                                    s.dispatchEvent(new MessageEvent("message", {data: b})), i = i.slice(l.pos);
                                    continue
                                }
                                break
                            }
                        }
                        if (r) break
                    }
                } catch (r) {
                    s.dispatchEvent(new Event("close"))
                }
            })
        }

        close() {
            return E(this, null, function* () {
                try {
                    this._writer && (yield this._writer.close()), this._transport.close()
                } catch (s) {
                }
            })
        }

        send(s) {
            return E(this, null, function* () {
                let e;
                this._protocol === "json" ? e = new TextEncoder().encode(s + `
`) : e = s;
                try {
                    yield this._writer.write(e)
                } catch (t) {
                    this.close()
                }
            })
        }
    };
    var z = class {
        encodeCommands(s) {
            return s.map(e => JSON.stringify(e)).join(`
`)
        }
    }, W = class {
        decodeReplies(s) {
            return s.trim().split(`
`).map(e => JSON.parse(e))
        }
    };
    var he = X(H()), we = {
        protocol: "json",
        token: null,
        getToken: null,
        data: null,
        debug: !1,
        name: "js",
        version: "",
        fetch: null,
        readableStream: null,
        websocket: null,
        eventsource: null,
        sockjs: null,
        sockjsOptions: {},
        emulationEndpoint: "/emulation",
        minReconnectDelay: 500,
        maxReconnectDelay: 2e4,
        timeout: 5e3,
        maxServerPingDelay: 1e4
    }, C = class extends he.default {
        constructor(e, t) {
            super();
            this._reconnectTimeout = null;
            this._refreshTimeout = null;
            this._serverPingTimeout = null;
            this.state = "disconnected", this._endpoint = e, this._emulation = !1, this._transports = [], this._currentTransportIndex = 0, this._triedAllTransports = !1, this._transportWasOpen = !1, this._transport = null, this._transportClosed = !0, this._encoder = null, this._decoder = null, this._reconnectTimeout = null, this._reconnectAttempts = 0, this._client = null, this._session = "", this._node = "", this._subs = {}, this._serverSubs = {}, this._commandId = 0, this._commands = [], this._batching = !1, this._refreshRequired = !1, this._refreshTimeout = null, this._callbacks = {}, this._token = void 0, this._dispatchPromise = Promise.resolve(), this._serverPing = 0, this._serverPingTimeout = null, this._sendPong = !1, this._promises = {}, this._promiseId = 0, this._debugEnabled = !1, this._config = q(q({}, we), t), this._configure(), this._debugEnabled ? (this.on("state", n => {
                this._debug("client state", n.oldState, "->", n.newState)
            }), this.on("error", n => {
                this._debug("client error", n)
            })) : this.on("error", function () {
                Function.prototype()
            })
        }

        newSubscription(e, t) {
            if (this.getSubscription(e) !== null) throw new Error("Subscription to the channel " + e + " already exists");
            let n = new I(this, e, t);
            return this._subs[e] = n, n
        }

        getSubscription(e) {
            return this._getSub(e)
        }

        removeSubscription(e) {
            !e || (e.state !== "unsubscribed" && e.unsubscribe(), this._removeSubscription(e))
        }

        subscriptions() {
            return this._subs
        }

        ready(e) {
            return this.state === "disconnected" ? Promise.reject({
                code: p.clientDisconnected,
                message: "client disconnected"
            }) : this.state === "connected" ? Promise.resolve() : new Promise((t, n) => {
                let i = {resolve: t, reject: n};
                e && (i.timeout = setTimeout(function () {
                    n({code: p.timeout, message: "timeout"})
                }, e)), this._promises[this._nextPromiseId()] = i
            })
        }

        connect() {
            if (this._isConnected()) {
                this._debug("connect called when already connected");
                return
            }
            if (this._isConnecting()) {
                this._debug("connect called when already connecting");
                return
            }
            this._reconnectAttempts = 0, this._startConnecting()
        }

        disconnect() {
            this._disconnect(O.disconnectCalled, "disconnect called", !1)
        }

        send(e) {
            let t = {send: {data: e}}, n = this;
            return this._methodCall().then(function () {
                return n._transportSendCommands([t]) ? Promise.resolve() : Promise.reject(n._createErrorObject(p.transportWriteError, "transport write error"))
            })
        }

        rpc(e, t) {
            let n = {rpc: {method: e, data: t}}, i = this;
            return this._methodCall().then(function () {
                return i._callPromise(n, function (r) {
                    return {data: r.rpc.data}
                })
            })
        }

        publish(e, t) {
            let n = {publish: {channel: e, data: t}}, i = this;
            return this._methodCall().then(function () {
                return i._callPromise(n, function () {
                    return {}
                })
            })
        }

        history(e, t) {
            let n = {history: this._getHistoryRequest(e, t)}, i = this;
            return this._methodCall().then(function () {
                return i._callPromise(n, function (r) {
                    let a = r.history, c = [];
                    if (a.publications) for (let l = 0; l < a.publications.length; l++) c.push(i._getPublicationContext(e, a.publications[l]));
                    return {publications: c, epoch: a.epoch || "", offset: a.offset || 0}
                })
            })
        }

        presence(e) {
            let t = {presence: {channel: e}}, n = this;
            return this._methodCall().then(function () {
                return n._callPromise(t, function (i) {
                    return {clients: i.presence.presence}
                })
            })
        }

        presenceStats(e) {
            let t = {presence_stats: {channel: e}}, n = this;
            return this._methodCall().then(function () {
                return n._callPromise(t, function (i) {
                    let r = i.presence_stats;
                    return {numUsers: r.num_users, numClients: r.num_clients}
                })
            })
        }

        startBatching() {
            this._batching = !0
        }

        stopBatching() {
            let e = this;
            Promise.resolve().then(function () {
                Promise.resolve().then(function () {
                    e._batching = !1, e._flush()
                })
            })
        }

        _debug(...e) {
            !this._debugEnabled || ce("debug", e)
        }

        _setFormat(e) {
            if (!this._formatOverride(e)) {
                if (e === "protobuf") throw new Error("not implemented by JSON-only Centrifuge client, use client with Protobuf support");
                this._encoder = new z, this._decoder = new W
            }
        }

        _formatOverride(e) {
            return !1
        }

        _configure() {
            if (!("Promise" in window)) throw new Error("Promise polyfill required");
            if (!this._endpoint) throw new Error("endpoint configuration required");
            if (this._config.protocol !== "json" && this._config.protocol !== "protobuf") throw new Error("unsupported protocol " + this._config.protocol);
            if (this._config.token !== null && (this._token = this._config.token), this._setFormat("json"), this._config.protocol === "protobuf" && this._setFormat("protobuf"), (this._config.debug === !0 || typeof localStorage != "undefined" && localStorage.getItem("centrifuge.debug")) && (this._debugEnabled = !0), this._debug("config", this._config), typeof this._endpoint != "string") if (typeof this._endpoint == "object" && this._endpoint instanceof Array) {
                this._transports = this._endpoint, this._emulation = !0;
                for (let e in this._transports) {
                    let t = this._transports[e];
                    if (!t.endpoint || !t.transport) throw new Error("malformed transport configuration");
                    let n = t.transport;
                    if (["websocket", "http_stream", "sse", "sockjs", "webtransport"].indexOf(n) < 0) throw new Error("unsupported transport name: " + n)
                }
            } else throw new Error("unsupported url configuration type: only string or array of objects are supported")
        }

        _setState(e) {
            if (this.state !== e) {
                let t = this.state;
                return this.state = e, this.emit("state", {newState: e, oldState: t}), !0
            }
            return !1
        }

        _isDisconnected() {
            return this.state === "disconnected"
        }

        _isConnecting() {
            return this.state === "connecting"
        }

        _isConnected() {
            return this.state === "connected"
        }

        _nextCommandId() {
            return ++this._commandId
        }

        _getReconnectDelay() {
            let e = S(this._reconnectAttempts, this._config.minReconnectDelay, this._config.maxReconnectDelay);
            return this._reconnectAttempts += 1, e
        }

        _clearOutgoingRequests() {
            for (let e in this._callbacks) if (this._callbacks.hasOwnProperty(e)) {
                let t = this._callbacks[e];
                clearTimeout(t.timeout);
                let n = t.errback;
                if (!n) continue;
                n({error: this._createErrorObject(p.connectionClosed, "connection closed")})
            }
            this._callbacks = {}
        }

        _clearConnectedState() {
            this._client = null, this._clearServerPingTimeout(), this._clearRefreshTimeout();
            for (let e in this._subs) {
                if (!this._subs.hasOwnProperty(e)) continue;
                let t = this._subs[e];
                t.state === "subscribed" && t._setSubscribing(L.transportClosed, "transport closed")
            }
            for (let e in this._serverSubs) this._serverSubs.hasOwnProperty(e) && this.emit("subscribing", {channel: e})
        }

        _handleWriteError(e) {
            for (let t of e) {
                let n = t.id;
                if (!(n in this._callbacks)) continue;
                let i = this._callbacks[n];
                clearTimeout(this._callbacks[n].timeout), delete this._callbacks[n], i.errback({error: this._createErrorObject(p.transportWriteError, "transport write error")})
            }
        }

        _transportSendCommands(e) {
            if (!e.length) return !0;
            if (!this._transport) return !1;
            try {
                this._transport.send(this._encoder.encodeCommands(e), this._session, this._node)
            } catch (t) {
                return this._debug("error writing commands", t), this._handleWriteError(e), !1
            }
            return !0
        }

        _initializeTransport() {
            let e;
            this._config.websocket !== null ? e = this._config.websocket : typeof window.WebSocket != "function" && typeof window.WebSocket != "object" || (e = window.WebSocket);
            let t = null;
            this._config.sockjs !== null ? t = this._config.sockjs : typeof window.SockJS != "undefined" && (t = window.SockJS);
            let n = null;
            this._config.eventsource !== null ? n = this._config.eventsource : typeof window.EventSource != "undefined" && (n = window.EventSource);
            let i = null;
            this._config.fetch !== null ? i = this._config.fetch : typeof window.fetch != "undefined" && (i = window.fetch);
            let r = null;
            if (this._config.readableStream !== null ? r = this._config.readableStream : typeof window.ReadableStream != "undefined" && (r = window.ReadableStream), this._emulation) {
                this._currentTransportIndex >= this._transports.length && (this._triedAllTransports = !0, this._currentTransportIndex = 0);
                let h = 0;
                for (; ;) {
                    if (h >= this._transports.length) throw new Error("no supported transport found");
                    let _ = this._transports[this._currentTransportIndex], f = _.transport, d = _.endpoint;
                    if (f === "websocket") {
                        if (this._debug("trying websocket transport"), this._transport = new T(d, {websocket: e}), !this._transport.supported()) {
                            this._debug("websocket transport not available"), this._currentTransportIndex++, h++;
                            continue
                        }
                    } else if (f === "webtransport") {
                        if (this._debug("trying webtransport transport"), this._transport = new N(d, {
                            webtransport: window.WebTransport,
                            decoder: this._decoder,
                            encoder: this._encoder
                        }), !this._transport.supported()) {
                            this._debug("webtransport transport not available"), this._currentTransportIndex++, h++;
                            continue
                        }
                    } else if (f === "http_stream") {
                        if (this._debug("trying http_stream transport"), this._transport = new A(d, {
                            fetch: i,
                            readableStream: r,
                            emulationEndpoint: this._config.emulationEndpoint,
                            decoder: this._decoder,
                            encoder: this._encoder
                        }), !this._transport.supported()) {
                            this._debug("http_stream transport not available"), this._currentTransportIndex++, h++;
                            continue
                        }
                    } else if (f === "sse") {
                        if (this._debug("trying sse transport"), this._transport = new M(d, {
                            eventsource: n,
                            fetch: i,
                            emulationEndpoint: this._config.emulationEndpoint
                        }), !this._transport.supported()) {
                            this._debug("sse transport not available"), this._currentTransportIndex++, h++;
                            continue
                        }
                    } else if (f === "sockjs") {
                        if (this._debug("trying sockjs"), this._transport = new U(d, {
                            sockjs: t,
                            sockjsOptions: this._config.sockjsOptions
                        }), !this._transport.supported()) {
                            this._debug("sockjs transport not available"), this._currentTransportIndex++, h++;
                            continue
                        }
                    } else throw new Error("unknown transport " + f);
                    break
                }
            } else {
                if (ae(this._endpoint, "http")) throw new Error("Provide explicit transport endpoints configuration in case of using HTTP (i.e. using array of TransportEndpoint instead of a single string), or use ws(s):// scheme in an endpoint if you aimed using WebSocket transport");
                if (this._debug("client will use websocket"), this._transport = new T(this._endpoint, {websocket: e}), !this._transport.supported()) throw new Error("WebSocket not available")
            }
            let a = this, c, l = !1, b = !0;
            this._transport.name() === "sse" && (b = !1);
            let m = [];
            if (this._transport.emulation()) {
                let h = a._sendConnect(!0);
                if (m.push(h), b) {
                    let _ = a._sendSubscribeCommands(!0, !0);
                    for (let f in _) m.push(_[f])
                }
            }
            let w = this._encoder.encodeCommands(m);
            this._transport.initialize(this._config.protocol, {
                onOpen: function () {
                    l = !0, c = a._transport.subName(), a._debug(c, "transport open"), a._transportWasOpen = !0, a._transportClosed = !1, !a._transport.emulation() && (a.startBatching(), a._sendConnect(!1), b && a._sendSubscribeCommands(!0, !1), a.stopBatching())
                }, onError: function (h) {
                    a._debug("transport level error", h)
                }, onClose: function (h) {
                    a._debug(a._transport.name(), "transport closed"), a._transportClosed = !0;
                    let _ = "connection closed", f = !0, d = 0;
                    if (h && "code" in h && h.code && (d = h.code), h && h.reason) try {
                        let g = JSON.parse(h.reason);
                        _ = g.reason, f = g.reconnect
                    } catch (g) {
                        _ = h.reason, (d >= 3500 && d < 4e3 || d >= 4500 && d < 5e3) && (f = !1)
                    }
                    d < 3e3 ? (d === 1009 ? (d = O.messageSizeLimit, _ = "message size limit exceeded", f = !1) : (d = v.transportClosed, _ = "transport closed"), a._emulation && !a._transportWasOpen && (a._currentTransportIndex++, a._currentTransportIndex >= a._transports.length && (a._triedAllTransports = !0, a._currentTransportIndex = 0))) : a._transportWasOpen = !0;
                    let P = !1;
                    if (a._emulation && !a._transportWasOpen && !a._triedAllTransports && (P = !0), a._isConnecting() && !l && a.emit("error", {
                        type: "transport",
                        error: {code: p.transportClosed, message: "transport closed"},
                        transport: a._transport.name()
                    }), a._disconnect(d, _, f), a._isConnecting()) {
                        let g = a._getReconnectDelay();
                        P && (g = 0), a._debug("reconnect after " + g + " milliseconds"), a._reconnectTimeout = setTimeout(() => {
                            a._startReconnecting()
                        }, g)
                    }
                }, onMessage: function (h) {
                    a._dataReceived(h)
                }
            }, w)
        }

        _sendConnect(e) {
            let t = this._constructConnectCommand(), n = this;
            return this._call(t, e).then(i => {
                let r = i.reply.connect;
                n._connectResponse(r), i.next && i.next()
            }, i => {
                n._connectError(i.error), i.next && i.next()
            }), t
        }

        _startReconnecting() {
            if (!this._isConnecting()) return;
            if (!(this._refreshRequired || !this._token && this._config.getToken !== null)) {
                this._initializeTransport();
                return
            }
            let t = this;
            this._getToken().then(function (n) {
                if (!!t._isConnecting()) {
                    if (!n) {
                        t._failUnauthorized();
                        return
                    }
                    t._token = n, t._debug("connection token refreshed"), t._initializeTransport()
                }
            }).catch(function (n) {
                if (!t._isConnecting()) return;
                t.emit("error", {
                    type: "connectToken",
                    error: {code: p.clientConnectToken, message: n !== void 0 ? n.toString() : ""}
                });
                let i = t._getReconnectDelay();
                t._debug("error on connection token refresh, reconnect after " + i + " milliseconds", n), t._reconnectTimeout = setTimeout(() => {
                    t._startReconnecting()
                }, i)
            })
        }

        _connectError(e) {
            this.state === "connecting" && (e.code === 109 && (this._refreshRequired = !0), e.code < 100 || e.temporary === !0 || e.code === 109 ? (this.emit("error", {
                type: "connect",
                error: e
            }), this._transport && !this._transportClosed && (this._transportClosed = !0, this._transport.close())) : this._disconnect(e.code, e.message, !1))
        }

        _constructConnectCommand() {
            let e = {};
            this._token && (e.token = this._token), this._config.data && (e.data = this._config.data), this._config.name && (e.name = this._config.name), this._config.version && (e.version = this._config.version);
            let t = {}, n = !1;
            for (let i in this._serverSubs) if (this._serverSubs.hasOwnProperty(i) && this._serverSubs[i].recoverable) {
                n = !0;
                let r = {recover: !0};
                this._serverSubs[i].offset && (r.offset = this._serverSubs[i].offset), this._serverSubs[i].epoch && (r.epoch = this._serverSubs[i].epoch), t[i] = r
            }
            return n && (e.subs = t), {connect: e}
        }

        _getHistoryRequest(e, t) {
            let n = {channel: e};
            return t !== void 0 && (t.since && (n.since = {offset: t.since.offset}, t.since.epoch && (n.since.epoch = t.since.epoch)), t.limit !== void 0 && (n.limit = t.limit), t.reverse === !0 && (n.reverse = !0)), n
        }

        _methodCall() {
            return this._isConnected() ? Promise.resolve() : new Promise((e, t) => {
                let n = setTimeout(function () {
                    t({code: p.timeout, message: "timeout"})
                }, this._config.timeout);
                this._promises[this._nextPromiseId()] = {timeout: n, resolve: e, reject: t}
            })
        }

        _callPromise(e, t) {
            return new Promise((n, i) => {
                this._call(e, !1).then(r => {
                    n(t(r.reply)), r.next && r.next()
                }, r => {
                    i(r.error), r.next && r.next()
                })
            })
        }

        _dataReceived(e) {
            this._serverPing > 0 && this._waitServerPing();
            let t = this._decoder.decodeReplies(e);
            this._dispatchPromise = this._dispatchPromise.then(() => {
                let n;
                this._dispatchPromise = new Promise(i => {
                    n = i
                }), this._dispatchSynchronized(t, n)
            })
        }

        _dispatchSynchronized(e, t) {
            let n = Promise.resolve();
            for (let i in e) e.hasOwnProperty(i) && (n = n.then(() => this._dispatchReply(e[i])));
            n = n.then(() => {
                t()
            })
        }

        _dispatchReply(e) {
            let t, n = new Promise(r => {
                t = r
            });
            if (e == null) return this._debug("dispatch: got undefined or null reply"), t(), n;
            let i = e.id;
            return i && i > 0 ? this._handleReply(e, t) : e.push ? this._handlePush(e.push, t) : this._handleServerPing(t), n
        }

        _call(e, t) {
            return new Promise((n, i) => {
                e.id = this._nextCommandId(), this._registerCall(e.id, n, i), t || this._addCommand(e)
            })
        }

        _startConnecting() {
            this._debug("start connecting"), this._setState("connecting") && this.emit("connecting", {
                code: v.connectCalled,
                reason: "connect called"
            }), this._client = null, this._startReconnecting()
        }

        _disconnect(e, t, n) {
            if (this._isDisconnected()) return;
            let i = this.state, r = {code: e, reason: t}, a = !1;
            n ? a = this._setState("connecting") : (a = this._setState("disconnected"), this._rejectPromises({
                code: p.clientDisconnected,
                message: "disconnected"
            })), this._clearOutgoingRequests(), i === "connecting" && this._clearReconnectTimeout(), i === "connected" && this._clearConnectedState(), a && (this._isConnecting() ? this.emit("connecting", r) : this.emit("disconnected", r)), this._transport && !this._transportClosed && (this._transportClosed = !0, this._transport.close())
        }

        _failUnauthorized() {
            this._disconnect(O.unauthorized, "unauthorized", !1)
        }

        _getToken() {
            if (this._debug("get connection token"), !this._config.getToken) throw new Error("provide a function to get connection token");
            return this._config.getToken({})
        }

        _refresh() {
            let e = this._client, t = this;
            this._getToken().then(function (n) {
                if (e !== t._client) return;
                if (!n) {
                    t._failUnauthorized();
                    return
                }
                if (t._token = n, t._debug("connection token refreshed"), !t._isConnected()) return;
                let i = {refresh: {token: t._token}};
                t._call(i, !1).then(r => {
                    let a = r.reply.refresh;
                    t._refreshResponse(a), r.next && r.next()
                }, r => {
                    t._refreshError(r.error), r.next && r.next()
                })
            }).catch(function (n) {
                t.emit("error", {
                    type: "refreshToken",
                    error: {code: p.clientRefreshToken, message: n !== void 0 ? n.toString() : ""}
                }), t._refreshTimeout = setTimeout(() => t._refresh(), t._getRefreshRetryDelay())
            })
        }

        _refreshError(e) {
            e.code < 100 || e.temporary === !0 ? (this.emit("error", {
                type: "refresh",
                error: e
            }), this._refreshTimeout = setTimeout(() => this._refresh(), this._getRefreshRetryDelay())) : this._disconnect(e.code, e.message, !1)
        }

        _getRefreshRetryDelay() {
            return S(0, 5e3, 1e4)
        }

        _refreshResponse(e) {
            this._refreshTimeout && (clearTimeout(this._refreshTimeout), this._refreshTimeout = null), e.expires && (this._client = e.client, this._refreshTimeout = setTimeout(() => this._refresh(), x(e.ttl)))
        }

        _removeSubscription(e) {
            e !== null && delete this._subs[e.channel]
        }

        _unsubscribe(e) {
            if (!this._isConnected()) return;
            let n = {unsubscribe: {channel: e.channel}}, i = this;
            this._call(n, !1).then(r => {
                r.next && r.next()
            }, r => {
                r.next && r.next(), i._disconnect(v.unsubscribeError, "unsubscribe error", !0)
            })
        }

        _getSub(e) {
            let t = this._subs[e];
            return t || null
        }

        _isServerSub(e) {
            return this._serverSubs[e] !== void 0
        }

        _sendSubscribeCommands(e, t) {
            let n = [];
            for (let i in this._subs) {
                if (!this._subs.hasOwnProperty(i)) continue;
                let r = this._subs[i];
                if (r._inflight !== !0 && r.state === "subscribing") {
                    let a = r._subscribe(e, t);
                    a && n.push(a)
                }
            }
            return n
        }

        _connectResponse(e) {
            if (this._transportWasOpen = !0, this._reconnectAttempts = 0, this._refreshRequired = !1, this._isConnected()) return;
            this._client = e.client, this._setState("connected"), this._refreshTimeout && clearTimeout(this._refreshTimeout), e.expires && (this._refreshTimeout = setTimeout(() => this._refresh(), x(e.ttl))), this._session = e.session, this._node = e.node, this.startBatching(), this._sendSubscribeCommands(!1, !1), this.stopBatching();
            let t = {client: e.client, transport: this._transport.subName()};
            e.data && (t.data = e.data), this.emit("connected", t), this._resolvePromises(), this._processServerSubs(e.subs || {}), e.ping && e.ping > 0 ? (this._serverPing = e.ping * 1e3, this._sendPong = e.pong === !0, this._waitServerPing()) : this._serverPing = 0
        }

        _processServerSubs(e) {
            for (let t in e) {
                if (!e.hasOwnProperty(t)) continue;
                let n = e[t];
                this._serverSubs[t] = {offset: n.offset, epoch: n.epoch, recoverable: n.recoverable || !1};
                let i = this._getSubscribeContext(t, n);
                this.emit("subscribed", i)
            }
            for (let t in e) {
                if (!e.hasOwnProperty(t)) continue;
                let n = e[t];
                if (n.recovered) {
                    let i = n.publications;
                    if (i && i.length > 0) for (let r in i) i.hasOwnProperty(r) && this._handlePublication(t, i[r])
                }
            }
            for (let t in this._serverSubs) !this._serverSubs.hasOwnProperty(t) || e[t] || (this.emit("unsubscribed", {channel: t}), delete this._serverSubs[t])
        }

        _clearRefreshTimeout() {
            this._refreshTimeout !== null && (clearTimeout(this._refreshTimeout), this._refreshTimeout = null)
        }

        _clearReconnectTimeout() {
            this._reconnectTimeout !== null && (clearTimeout(this._reconnectTimeout), this._reconnectTimeout = null)
        }

        _clearServerPingTimeout() {
            this._serverPingTimeout !== null && (clearTimeout(this._serverPingTimeout), this._serverPingTimeout = null)
        }

        _waitServerPing() {
            this._config.maxServerPingDelay !== 0 && (!this._isConnected() || (this._clearServerPingTimeout(), this._serverPingTimeout = setTimeout(() => {
                !this._isConnected() || this._disconnect(v.noPing, "no ping", !0)
            }, this._serverPing + this._config.maxServerPingDelay)))
        }

        _getSubscribeContext(e, t) {
            let n = {channel: e, positioned: !1, recoverable: !1, wasRecovering: !1, recovered: !1};
            t.recovered && (n.recovered = !0), t.positioned && (n.positioned = !0), t.recoverable && (n.recoverable = !0), t.was_recovering && (n.wasRecovering = !0);
            let i = "";
            "epoch" in t && (i = t.epoch);
            let r = 0;
            return "offset" in t && (r = t.offset), (n.positioned || n.recoverable) && (n.streamPosition = {
                offset: r,
                epoch: i
            }), t.data && (n.data = t.data), n
        }

        _handleReply(e, t) {
            let n = e.id;
            if (!(n in this._callbacks)) {
                t();
                return
            }
            let i = this._callbacks[n];
            if (clearTimeout(this._callbacks[n].timeout), delete this._callbacks[n], ue(e)) {
                let r = i.errback;
                if (!r) {
                    t();
                    return
                }
                let a = e.error;
                r({error: a, next: t})
            } else {
                let r = i.callback;
                if (!r) return;
                r({reply: e, next: t})
            }
        }

        _handleJoin(e, t) {
            let n = this._getSub(e);
            if (!n) {
                if (this._isServerSub(e)) {
                    let i = {channel: e, info: this._getJoinLeaveContext(t.info)};
                    this.emit("join", i)
                }
                return
            }
            n._handleJoin(t)
        }

        _handleLeave(e, t) {
            let n = this._getSub(e);
            if (!n) {
                if (this._isServerSub(e)) {
                    let i = {channel: e, info: this._getJoinLeaveContext(t.info)};
                    this.emit("leave", i)
                }
                return
            }
            n._handleLeave(t)
        }

        _handleUnsubscribe(e, t) {
            let n = this._getSub(e);
            if (!n) {
                this._isServerSub(e) && (delete this._serverSubs[e], this.emit("unsubscribed", {channel: e}));
                return
            }
            t.code < 2500 ? n._setUnsubscribed(t.code, t.reason, !1) : n._setSubscribing(t.code, t.reason)
        }

        _handleSubscribe(e, t) {
            this._serverSubs[e] = {
                offset: t.offset,
                epoch: t.epoch,
                recoverable: t.recoverable || !1
            }, this.emit("subscribed", this._getSubscribeContext(e, t))
        }

        _handleDisconnect(e) {
            let t = e.code, n = !0;
            (t >= 3500 && t < 4e3 || t >= 4500 && t < 5e3) && (n = !1), this._disconnect(t, e.reason, n)
        }

        _getPublicationContext(e, t) {
            let n = {channel: e, data: t.data};
            return t.offset && (n.offset = t.offset), t.info && (n.info = this._getJoinLeaveContext(t.info)), t.tags && (n.tags = t.tags), n
        }

        _getJoinLeaveContext(e) {
            let t = {client: e.client, user: e.user};
            return e.conn_info && (t.connInfo = e.conn_info), e.chan_info && (t.chanInfo = e.chan_info), t
        }

        _handlePublication(e, t) {
            let n = this._getSub(e);
            if (!n) {
                if (this._isServerSub(e)) {
                    let i = this._getPublicationContext(e, t);
                    this.emit("publication", i), t.offset !== void 0 && (this._serverSubs[e].offset = t.offset)
                }
                return
            }
            n._handlePublication(t)
        }

        _handleMessage(e) {
            this.emit("message", {data: e.data})
        }

        _handleServerPing(e) {
            if (this._sendPong) {
                let t = {};
                this._transportSendCommands([t])
            }
            e()
        }

        _handlePush(e, t) {
            let n = e.channel;
            e.pub ? this._handlePublication(n, e.pub) : e.message ? this._handleMessage(e.message) : e.join ? this._handleJoin(n, e.join) : e.leave ? this._handleLeave(n, e.leave) : e.unsubscribe ? this._handleUnsubscribe(n, e.unsubscribe) : e.subscribe ? this._handleSubscribe(n, e.subscribe) : e.disconnect && this._handleDisconnect(e.disconnect), t()
        }

        _flush() {
            let e = this._commands.slice(0);
            this._commands = [], this._transportSendCommands(e)
        }

        _createErrorObject(e, t, n) {
            let i = {code: e, message: t};
            return n && (i.temporary = !0), i
        }

        _registerCall(e, t, n) {
            this._callbacks[e] = {
                callback: t,
                errback: n,
                timeout: null
            }, this._callbacks[e].timeout = setTimeout(() => {
                delete this._callbacks[e], K(n) && n({error: this._createErrorObject(p.timeout, "timeout")})
            }, this._config.timeout)
        }

        _addCommand(e) {
            this._batching ? this._commands.push(e) : this._transportSendCommands([e])
        }

        _nextPromiseId() {
            return ++this._promiseId
        }

        _resolvePromises() {
            for (let e in this._promises) this._promises[e].timeout && clearTimeout(this._promises[e].timeout), this._promises[e].resolve(), delete this._promises[e]
        }

        _rejectPromises(e) {
            for (let t in this._promises) this._promises[t].timeout && clearTimeout(this._promises[t].timeout), this._promises[t].reject(e), delete this._promises[t]
        }
    };
    C.SubscriptionState = D;
    C.State = j;
    window.Centrifuge = C;
})();
//# sourceMappingURL=centrifuge.js.map
